<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminLateReturnNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Loan $loan,
        private readonly int $lateDays,
        private readonly int $fine
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $memberName = $this->loan->member?->nama ?? 'Siswa';
        $bookTitle = $this->loan->book?->judul ?? 'Buku';

        return [
            'type' => 'admin_late_return',
            'loan_id' => $this->loan->id,
            'member_name' => $memberName,
            'book_title' => $bookTitle,
            'late_days' => $this->lateDays,
            'fine' => $this->fine,
            'returned_at' => $this->loan->tgl_dikembalikan?->format('Y-m-d'),
            'message' => "{$memberName} mengembalikan buku \"{$bookTitle}\" terlambat {$this->lateDays} hari. Denda: Rp" . number_format($this->fine, 0, ',', '.'),
        ];
    }
}
