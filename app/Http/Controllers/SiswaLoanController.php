<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Support\LibrarySetting;
use App\Traits\NotifiesAdminsOnLateReturn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SiswaLoanController extends Controller
{
    use NotifiesAdminsOnLateReturn;

    public function index(): View
    {
        $member = auth()->user()->member;
        $loans = Loan::with('book')
            ->when($member, fn ($query) => $query->where('member_id', $member->id))
            ->unless($member, fn ($query) => $query->whereRaw('1 = 0'))
            ->latest()
            ->paginate(10);

        return view('siswa.loans', [
            'title' => 'Pinjaman Saya',
            'breadcrumb' => ['Pinjaman Saya'],
            'loans' => $loans,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $member = auth()->user()->member;

        if (! $member) {
            return redirect()
                ->route('siswa.dashboard')
                ->with('error', 'Akun Anda belum terhubung ke data anggota. Hubungi admin perpustakaan.');
        }

        return view('siswa.loan-create', [
            'title' => 'Pinjam Buku',
            'breadcrumb' => ['Pinjaman Saya', 'Pinjam Buku'],
            'books' => Book::where('tersedia', '>', 0)->orderBy('judul')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $member = auth()->user()->member;

        if (! $member) {
            return redirect()
                ->route('siswa.dashboard')
                ->with('error', 'Akun Anda belum terhubung ke data anggota. Hubungi admin perpustakaan.');
        }

        $validated = $request->validate([
            'book_id'    => 'required|exists:books,id',
            'jumlah'     => 'required|integer|min:1',
            'tgl_pinjam' => 'required|date|after_or_equal:today',
        ]);

        $book = Book::findOrFail($validated['book_id']);

        if ($book->tersedia < $validated['jumlah']) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'jumlah' => 'Stok buku tidak mencukupi. Tersedia: ' . $book->tersedia,
                ]);
        }

        DB::transaction(function () use ($validated, $book, $member) {
            Loan::create([
                'member_id'   => $member->id,
                'book_id'     => $book->id,
                'jumlah'      => $validated['jumlah'],
                'tgl_pinjam'  => $validated['tgl_pinjam'],
                'tgl_kembali' => Carbon::parse($validated['tgl_pinjam'])->addDays(7),
                'status'      => 'dipinjam',
                'denda'       => 0,
            ]);

            $book->decrement('tersedia', $validated['jumlah']);
        });

        return redirect()
            ->route('siswa.loans.index')
            ->with('success', 'Peminjaman berhasil diajukan.');
    }

    public function processReturn(Loan $loan): RedirectResponse
    {
        $member = auth()->user()->member;

        // Pastikan pinjaman ini benar milik siswa yang login
        if (! $member || $loan->member_id !== $member->id) {
            abort(403);
        }

        if (! in_array($loan->status, ['dipinjam', 'terlambat'], true)) {
            return redirect()
                ->route('siswa.loans.index')
                ->with('error', 'Peminjaman ini sudah dikembalikan sebelumnya.');
        }

        $lateDays = 0;
        $denda = 0;

        DB::transaction(function () use ($loan, &$lateDays, &$denda) {
            $today = today();
            $lateDays = max(0, $today->diffInDays($loan->tgl_kembali, false) * -1);
            $denda = $lateDays * LibrarySetting::lateFeePerDay();

            $loan->update([
                'tgl_dikembalikan' => $today,
                'status' => 'dikembalikan',
                'denda' => $denda,
            ]);

            $loan->book()->increment('tersedia');
        });

        $this->notifyAdminsLateReturn($loan, $lateDays, $denda);

        return redirect()
            ->route('siswa.loans.index')
            ->with('success', 'Pengembalian buku berhasil dicatat.');
    }
}