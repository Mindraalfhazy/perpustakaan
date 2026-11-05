<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $jenis = $request->string('jenis')->toString() ?: 'Peminjaman';
        $bulan = (int) ($request->bulan ?: now()->month);
        $tahun = (int) ($request->tahun ?: now()->year);

        $query = Loan::with(['member', 'book'])
            ->whereYear('tgl_pinjam', $tahun)
            ->whereMonth('tgl_pinjam', $bulan);

        if ($jenis === 'Pengembalian') {
            $query->whereNotNull('tgl_dikembalikan');
        }

        if ($jenis === 'Denda') {
            $query->where('denda', '>', 0);
        }

        $loans = $query->latest()->get();

        return view('reports.index', [
            'title' => 'Laporan',
            'breadcrumb' => ['Dashboard', 'Laporan'],
            'jenis' => $jenis,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'loans' => $loans,
            'totalPeminjaman' => (clone $query)->count(),
            'totalPengembalian' => (clone $query)->whereNotNull('tgl_dikembalikan')->count(),
            'bukuTerlambat' => (clone $query)->where('status', 'terlambat')->count(),
            'totalDenda' => (clone $query)->sum('denda'),
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $jenis = $request->string('jenis')->toString() ?: 'Peminjaman';
        $bulan = (int) ($request->bulan ?: now()->month);
        $tahun = (int) ($request->tahun ?: now()->year);

        $query = Loan::with(['member', 'book'])
            ->whereYear('tgl_pinjam', $tahun)
            ->whereMonth('tgl_pinjam', $bulan);

        if ($jenis === 'Pengembalian') {
            $query->whereNotNull('tgl_dikembalikan');
        }

        if ($jenis === 'Denda') {
            $query->where('denda', '>', 0);
        }

        $loans = $query->latest()->get();

        $pdf = Pdf::loadView('reports.pdf', [
            'jenis' => $jenis,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'loans' => $loans,
        ])->setPaper('a4', 'landscape');

        $namaFile = 'laporan-' . strtolower($jenis) . '-' . $bulan . '-' . $tahun . '.pdf';

        return $pdf->download($namaFile);
    }
}