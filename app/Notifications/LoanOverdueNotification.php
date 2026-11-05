<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Support\LibrarySetting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoanOverdueNotification extends Notification
{
    use Queueable;

    private int $lateDays;
    private int $fine;

    public function __construct(private readonly Loan $loan, ?int $dendaPerHari = null)
    {
        $this->lateDays = $this->loan->tgl_kembali
            ? max(0, now()->startOfDay()->diffInDays($this->loan->tgl_kembali, false) * -1)
            : 0;

        $dendaPerHari ??= LibrarySetting::lateFeePerDay();
        $this->fine = $this->lateDays * $dendaPerHari;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'loan_overdue',
            'loan_id' => $this->loan->id,
            'book_title' => $this->loan->book?->judul ?? 'Buku',
            'late_days' => $this->lateDays,
            'fine' => $this->fine,
            'message' => "Buku \"{$this->loan->book?->judul}\" terlambat {$this->lateDays} hari. Denda: Rp" . number_format($this->fine, 0, ',', '.'),
        ];
    }
}
