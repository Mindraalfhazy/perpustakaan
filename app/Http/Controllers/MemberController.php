<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberStoreRequest;
use App\Http\Requests\MemberUpdateRequest;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $members = Member::query()
            ->with('user')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nama', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('members.index', [
            'title' => 'Data Anggota Siswa',
            'breadcrumb' => ['Dashboard', 'Data Anggota'],
            'members' => $members,
            'search' => $search,
            'kelasOptions' => $this->kelasOptions(),
        ]);
    }

    public function create(): View
    {
        return view('members.create', [
            'title' => 'Tambah Anggota Baru',
            'breadcrumb' => ['Dashboard', 'Data Anggota', 'Tambah Anggota'],
            'member' => new Member(),
            'kelasOptions' => $this->kelasOptions(),
        ]);
    }

    public function store(MemberStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $memberData = Arr::except($data, ['password']);
            $memberData['user_id'] = $this->syncMemberUser($data)?->id;

            Member::create($memberData);
        });

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function show(Member $member): RedirectResponse
    {
        return redirect()->route('admin.members.edit', $member);
    }

    public function edit(Member $member): View
    {
        return view('members.edit', [
            'title' => 'Edit Data Anggota',
            'breadcrumb' => ['Dashboard', 'Data Anggota', 'Edit Anggota'],
            'member' => $member,
            'kelasOptions' => $this->kelasOptions(),
        ]);
    }

    public function update(MemberUpdateRequest $request, Member $member): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $member) {
            $memberData = Arr::except($data, ['password']);
            $memberData['user_id'] = $this->syncMemberUser($data, $member->user_id)?->id;

            $member->update($memberData);
        });

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Anggota berhasil diperbarui.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        $member->delete();

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Anggota berhasil dihapus.');
    }

    private function kelasOptions(): array
    {
        $tingkat = ['VII', 'VIII', 'IX'];
        $rombel = range('A', 'J');

        $options = [];

        foreach ($tingkat as $t) {
            foreach ($rombel as $r) {
                $options[] = "{$t} {$r}";
            }
        }

        return $options;
    }

    private function syncMemberUser(array $data, ?int $currentUserId = null): ?User
    {
        $user = $this->resolveMemberUser($data['nisn'], $data['email'] ?? null, $currentUserId);

        if (!$user && blank($data['password'] ?? null)) {
            return null;
        }

        if (!$user) {
            $user = new User([
                'email_verified_at' => now(),
                'role' => User::ROLE_SISWA,
            ]);
        }

        $user->fill([
            'name' => $data['nama'],
            'email' => $data['email'],
            'role' => User::ROLE_SISWA,
            'nisn' => $data['nisn'],
            'kelas' => $data['kelas'],
        ]);

        if (filled($data['password'] ?? null)) {
            $user->password = $data['password'];
        }

        $user->save();

        return $user;
    }

    private function resolveMemberUser(string $nisn, ?string $email, ?int $currentUserId = null): ?User
    {
        if ($currentUserId) {
            $currentUser = User::query()->find($currentUserId);

            if ($currentUser?->isSiswa()) {
                return $currentUser;
            }
        }

        return User::query()
            ->where('role', User::ROLE_SISWA)
            ->where(function ($query) use ($nisn, $email) {
                $query->where('nisn', $nisn);

                if ($email) {
                    $query->orWhere('email', $email);
                }
            })
            ->first();
    }
}
