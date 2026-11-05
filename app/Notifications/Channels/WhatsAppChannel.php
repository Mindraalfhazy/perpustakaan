<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    /**
     * Kirim notifikasi lewat WhatsApp menggunakan gateway Fonnte.
     *
     * Daftar akun & dapatkan token di https://fonnte.com (ada free trial).
     * Alternatif lain: Twilio WhatsApp API, Wablas, dll — tinggal ganti isi method ini.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $data = $notification->toWhatsApp($notifiable);
        $phone = $data['phone'] ?? null;
        $message = $data['message'] ?? null;

        if (! $phone || ! $message) {
            return;
        }

        $token = config('services.fonnte.token');

        if (! $token) {
            Log::warning('WhatsAppChannel: FONNTE_TOKEN belum diatur di .env, notifikasi WA dilewati.');

            return;
        }

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->post('https://api.fonnte.com/send', [
            'target' => $this->normalizePhone($phone),
            'message' => $message,
        ]);

        if (! $response->successful()) {
            Log::error('WhatsAppChannel: gagal mengirim WA', [
                'phone' => $phone,
                'response' => $response->body(),
            ]);
        }
    }

    /**
     * Ubah nomor lokal (08xx) menjadi format internasional (628xx) yang dibutuhkan Fonnte.
     */
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone) ?? $phone;

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }
}
