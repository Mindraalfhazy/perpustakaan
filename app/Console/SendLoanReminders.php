<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Notifications\LoanDueSoonNotification;
use App\Notifications\LoanOverdueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendLoanReminders extends Command
{
    protected $signature = 'loans:send-reminders';

    protected $description = 'Kirim notifikasi (web/email/WA) untuk buku yang segera jatuh tempo atau sudah terlambat';

    public function handle(): int
    {
        $this->markOverdueLoans();
        $this->sendDueSoonReminders();
        $this->sendOverdueReminders();

        $this->info('Reminder peminjaman selesai dikirim.');

        return self::SUCCESS;
    }

    /**
     * Pastikan status loan yang sudah lewat tgl_kembali diupdate jadi 'terlambat'
     * SEBELUM reminder terlambat dikirim. Ini mencegah reminder tidak pernah
     * terkirim kalau MarkOverdueLoans belum/tidak sempat jalan duluan.
     */
    private function markOverdueLoans(): void
    {
        $affected = Loan::query()
            ->where('status', 'dipinjam')
            ->whereDate('tgl_kembali', '<', now()->toDateString())
            ->update(['status' => 'terlambat']);

        if ($affected > 0) {
            $this->line("Status diupdate jadi terlambat: {$affected} pinjaman.");
        }
    }

    /**
     * Kirim reminder untuk buku yang jatuh tempo besok (H-1), sekali per hari.
     */
    private function sendDueSoonReminders(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        $loans = Loan::query()
            ->where('status', 'dipinjam')
            ->whereDate('tgl_kembali', $tomorrow)
            ->with(['book', 'member.user'])
            ->get();

        $sent = 0;

        foreach ($loans as $loan) {
            $user = $loan->member?->user;

            if (! $user) {
                continue;
            }

            if ($this->alreadyNotifiedToday($user, 'loan_due_soon', $loan->id)) {
                continue;
            }

            $user->notify(new LoanDueSoonNotification($loan));
            $sent++;
        }

        $this->line("Reminder jatuh tempo: {$sent}/{$loans->count()} pinjaman diproses.");
    }

    /**
     * Kirim reminder untuk buku yang sudah berstatus terlambat, sekali per hari.
     */
    private function sendOverdueReminders(): void
    {
        $loans = Loan::query()
            ->where('status', 'terlambat')
            ->with(['book', 'member.user'])
            ->get();

        $sent = 0;

        foreach ($loans as $loan) {
            $user = $loan->member?->user;

            if (! $user) {
                continue;
            }

            if ($this->alreadyNotifiedToday($user, 'loan_overdue', $loan->id)) {
                continue;
            }

            $user->notify(new LoanOverdueNotification($loan));
            $sent++;
        }

        $this->line("Reminder keterlambatan: {$sent}/{$loans->count()} pinjaman diproses.");
    }

    /**
     * Cegah kirim notifikasi dobel di hari yang sama untuk loan yang sama.
     */
    private function alreadyNotifiedToday(object $notifiable, string $type, int $loanId): bool
    {
        return DB::table('notifications')
            ->where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->id)
            ->whereDate('created_at', now()->toDateString())
            ->where('data', 'like', '%"type":"' . $type . '"%')
            ->where('data', 'like', '%"loan_id":' . $loanId . '%')
            ->exists();
    }
}