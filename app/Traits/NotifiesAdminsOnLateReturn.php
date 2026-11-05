<?php

namespace App\Traits;

use App\Models\Loan;
use App\Models\User;
use App\Notifications\AdminLateReturnNotification;
use Illuminate\Support\Facades\Notification;

trait NotifiesAdminsOnLateReturn
{
    /**
     * Kirim notifikasi ke semua admin saat pengembalian buku terlambat.
     */
    protected function notifyAdminsLateReturn(Loan $loan, int $lateDays, int $denda): void
    {
        if ($lateDays <= 0) {
            return;
        }

        $admins = User::all()->filter(fn (User $user) => $user->isAdmin());

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new AdminLateReturnNotification($loan, $lateDays, $denda));
    }
}