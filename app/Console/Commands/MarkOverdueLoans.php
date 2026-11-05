<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Illuminate\Console\Command;

class MarkOverdueLoans extends Command
{
    protected $signature = 'loans:mark-overdue';

    protected $description = 'Menandai peminjaman yang sudah lewat tanggal kembali sebagai terlambat';

    public function handle(): void
    {
        $affected = Loan::where('status', 'dipinjam')
            ->where('tgl_kembali', '<', today())
            ->update(['status' => 'terlambat']);

        $this->info("{$affected} peminjaman ditandai terlambat.");
    }
}
