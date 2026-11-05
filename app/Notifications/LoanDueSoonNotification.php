<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoanDueSoonNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Loan $loan)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'loan_due_soon',
            'loan_id' => $this->loan->id,
            'book_title' => $this->loan->book?->judul ?? 'Buku',
            'due_date' => $this->loan->tgl_kembali?->format('Y-m-d'),
            'message' => "Buku \"{$this->loan->book?->judul}\" akan jatuh tempo pada {$this->loan->tgl_kembali?->format('d/m/Y')}.",
        ];
    }
}