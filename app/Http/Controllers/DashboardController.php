<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\EbookReadingLog;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        Loan::where('status', 'dipinjam')
            ->where('tgl_kembali', '<', today())
            ->update(['status' => 'terlambat']);

        $latestLoans = Loan::with(['member', 'book'])
            ->latest()
            ->take(5)
            ->get();

        $popularBooks = Loan::selectRaw('book_id, COUNT(*) as total_pinjam')
            ->with('book')
            ->groupBy('book_id')
            ->orderByDesc('total_pinjam')
            ->take(5)
            ->get();

        $ebookTrend = EbookReadingLog::query()
            ->selectRaw('DATE(opened_at) as activity_date')
            ->selectRaw('COUNT(*) as total_sessions')
            ->where('opened_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy(DB::raw('DATE(opened_at)'))
            ->orderBy('activity_date')
            ->get();

        return view('dashboard', [
            'title' => 'Dashboard',
            'breadcrumb' => ['Dashboard'],
            'totalBooks' => Book::count(),
            'totalMembers' => Member::count(),
            'activeLoans' => Loan::where('status', 'dipinjam')->count(),
            'totalFines' => Loan::where('status', 'dikembalikan')->sum('denda'),
            'ebookSessions' => EbookReadingLog::count(),
            'activeReaders' => EbookReadingLog::distinct('user_id')->count('user_id'),
            'latestLoans' => $latestLoans,
            'popularBooks' => $popularBooks,
            'ebookTrend' => $ebookTrend,
        ]);
    }
}
