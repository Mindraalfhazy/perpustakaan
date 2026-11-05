<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\View\View;

class SiswaDashboardController extends Controller
{
    public function index(): View
    {
        $member = auth()->user()->member;

        $activeLoans = collect();

        if ($member) {
            $activeLoans = Loan::with('book')
                ->where('member_id', $member->id)
                ->whereIn('status', ['dipinjam', 'terlambat'])
                ->latest()
                ->get();
        }

        return view('siswa.dashboard', [
            'title' => 'Beranda',
            'breadcrumb' => ['Beranda'],
            'member' => $member,
            'activeLoans' => $activeLoans,
        ]);
    }
}
