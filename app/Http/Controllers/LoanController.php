<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanUpdateRequest;
use App\Http\Requests\LoanStoreRequest;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use App\Support\LibrarySetting;
use App\Traits\NotifiesAdminsOnLateReturn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LoanController extends Controller
{
    use NotifiesAdminsOnLateReturn;

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = $request->string('search')->toString();

        $loans = Loan::with(['member', 'book'])
            ->when($status === 'terlambat', function ($query) {
                $query->where(function ($q) {
                    // Masih dipinjam & sudah lewat tanggal kembali (belum dikembalikan)
                    $q->whereIn('status', ['dipinjam', 'terlambat'])
                        ->where('tgl_kembali', '<', today());
                })->orWhere(function ($q) {
                    // Sudah dikembalikan tapi dulu terlambat (kena denda)
                    $q->where('status', 'dikembalikan')
                        ->where('denda', '>', 0);
                });
            })
            ->when($status === 'dipinjam', function ($query) {
                $query->where('status', 'dipinjam')
                    ->where('tgl_kembali', '>=', today());
            })
            ->when($status && !in_array($status, ['dipinjam', 'terlambat'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($search, function ($query) use ($search) {
                $query->whereHas('member', function ($memberQuery) use ($search) {
                    $memberQuery->where('nama', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('loans.index', [
            'title' => 'Data Peminjaman Buku',
            'breadcrumb' => ['Dashboard', 'Peminjaman'],
            'loans' => $loans,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('loans.create', [
            'title' => 'Catat Peminjaman Baru',
            'breadcrumb' => ['Dashboard', 'Peminjaman', 'Tambah'],
            'members' => Member::orderBy('nama')->get(),
            'books' => Book::orderBy('judul')->get(),
        ]);
    }

    public function store(LoanStoreRequest $request): RedirectResponse
    {
        $book = Book::findOrFail($request->book_id);

        if ($book->tersedia < $request->jumlah) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'book_id' => 'Stok buku tidak mencukupi. Tersedia: ' . $book->tersedia
                ]);
        }

        DB::transaction(function () use ($request, $book) {
            Loan::create([
                'member_id'   => $request->member_id,
                'book_id'     => $request->book_id,
                'jumlah'      => $request->jumlah,
                'tgl_pinjam'  => $request->tgl_pinjam,
                'tgl_kembali' => $request->tgl_kembali,
                'status'      => 'dipinjam',
                'denda'       => 0,
            ]);

            $book->decrement('tersedia', $request->jumlah);
        });

        return redirect()
            ->route('admin.loans.index')
            ->with('success', 'Peminjaman berhasil dicatat.');
    }

    public function show(Loan $loan): View
    {
        $loan->load(['member', 'book']);

        return view('loans.show', [
            'title' => 'Detail Peminjaman',
            'breadcrumb' => ['Dashboard', 'Peminjaman', 'Detail'],
            'loan' => $loan,
        ]);
    }

    public function edit(Loan $loan): View|RedirectResponse
    {
        if ($loan->status === 'dikembalikan') {
            return redirect()
                ->route('admin.loans.show', $loan)
                ->with('error', 'Peminjaman yang sudah dikembalikan tidak dapat diubah.');
        }

        return view('loans.edit', [
            'title' => 'Edit Data Peminjaman',
            'breadcrumb' => ['Dashboard', 'Peminjaman', 'Edit'],
            'loan' => $loan,
            'members' => Member::orderBy('nama')->get(),
            'books' => Book::orderBy('judul')->get(),
        ]);
    }

    public function update(LoanUpdateRequest $request, Loan $loan): RedirectResponse
    {
        if ($loan->status === 'dikembalikan') {
            return redirect()
                ->route('admin.loans.show', $loan)
                ->with('error', 'Peminjaman yang sudah dikembalikan tidak dapat diubah.');
        }

        $data = $request->validated();

        DB::transaction(function () use ($loan, $data) {
            $loan->refresh();
            $bookChanged = (int) $loan->book_id !== (int) $data['book_id'];

            if ($bookChanged) {
                $loan->book()->increment('tersedia', $loan->jumlah);

                $newBook = Book::query()->lockForUpdate()->findOrFail($data['book_id']);

                if ($newBook->tersedia < $loan->jumlah) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'book_id' => 'Stok buku tidak mencukupi. Tersedia: ' . $newBook->tersedia,
                    ]);
                }

                $newBook->decrement('tersedia', $loan->jumlah);
            }

            $lateDays = max(0, today()->diffInDays($data['tgl_kembali'], false) * -1);

            $loan->update([
                'member_id' => $data['member_id'],
                'book_id' => $data['book_id'],
                'tgl_pinjam' => $data['tgl_pinjam'],
                'tgl_kembali' => $data['tgl_kembali'],
                'status' => $lateDays > 0 ? 'terlambat' : 'dipinjam',
            ]);
        });

        return redirect()
            ->route('admin.loans.index')
            ->with('success', 'Data peminjaman berhasil diperbarui.');
    }

    public function destroy(Loan $loan): RedirectResponse
    {
        if (in_array($loan->status, ['dipinjam', 'terlambat'], true)) {
            $loan->book()->increment('tersedia', $loan->jumlah);
        }

        $loan->delete();

        return redirect()
            ->route('admin.loans.index')
            ->with('success', 'Data peminjaman berhasil dihapus.');
    }

    public function processReturn(Loan $loan): RedirectResponse
    {
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

            $loan->book()->increment('tersedia', $loan->jumlah);
        });

        $this->notifyAdminsLateReturn($loan, $lateDays, $denda);

        return redirect()
            ->route('admin.loans.index')
            ->with('success', 'Pengembalian buku berhasil diproses.');
    }
}