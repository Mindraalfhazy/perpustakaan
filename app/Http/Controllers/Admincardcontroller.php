<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCardController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $kelas = $request->string('kelas')->toString();

        $members = Member::query()
            ->with(['user.card'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            })
            ->when($kelas, fn ($query) => $query->where('kelas', $kelas))
            ->orderBy('kelas')
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        $kelasOptions = Member::query()
            ->whereNotNull('kelas')
            ->distinct()
            ->orderBy('kelas')
            ->pluck('kelas');

        return view('admin.cards.index', [
            'title' => 'Kartu Anggota Siswa',
            'breadcrumb' => ['Dashboard', 'Kartu Anggota'],
            'members' => $members,
            'search' => $search,
            'kelas' => $kelas,
            'kelasOptions' => $kelasOptions,
        ]);
    }

    /**
     * Terbitkan kartu anggota untuk satu siswa.
     */
    public function issue(Member $member): RedirectResponse
    {
        if (! $member->user_id) {
            return back()->with('error', "Siswa {$member->nama} belum terhubung ke akun user, tidak bisa diterbitkan kartu.");
        }

        $exists = Card::where('user_id', $member->user_id)->exists();

        if ($exists) {
            return back()->with('error', "Siswa {$member->nama} sudah punya kartu anggota.");
        }

        Card::create([
            'user_id' => $member->user_id,
            'number' => $this->generateNumber($member),
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'batch' => (int) now()->format('y'),
            'created_by' => auth()->user()->name ?? 'System',
        ]);

        return back()->with('success', "Kartu anggota berhasil diterbitkan untuk {$member->nama}.");
    }

    /**
     * Terbitkan kartu anggota sekaligus untuk satu kelas (yang belum punya kartu saja).
     */
    public function bulkIssue(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kelas' => 'required|string',
        ]);

        $members = Member::query()
            ->where('kelas', $validated['kelas'])
            ->whereNotNull('user_id')
            ->get();

        $issued = 0;
        $skipped = 0;

        foreach ($members as $member) {
            $exists = Card::where('user_id', $member->user_id)->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            Card::create([
                'user_id' => $member->user_id,
                'number' => $this->generateNumber($member),
                'start_date' => now(),
                'end_date' => now()->addYear(),
                'batch' => (int) now()->format('y'),
                'created_by' => auth()->user()->name ?? 'System',
            ]);

            $issued++;
        }

        return back()->with(
            'success',
            "Berhasil menerbitkan {$issued} kartu baru untuk kelas {$validated['kelas']}. {$skipped} siswa sudah punya kartu sebelumnya."
        );
    }

    private function generateNumber(Member $member): string
    {
        return 'LIB-' . str_pad((string) $member->id, 6, '0', STR_PAD_LEFT);
    }
}