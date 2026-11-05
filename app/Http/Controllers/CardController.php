<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class CardController extends Controller
{
    /**
     * Generate & download kartu anggota (PDF) milik siswa yang sedang login.
     */
    public function download(): Response|RedirectResponse
    {
        $user = auth()->user();
        $card = $user->card;

        if (! $card) {
            return redirect()
                ->route('siswa.dashboard')
                ->with('error', 'Kartu anggota Anda belum diterbitkan. Silakan hubungi admin perpustakaan.');
        }

        $member = $user->member;

        $cardMemberName = $member?->nama ?? $user->name;
        $cardMemberNo = $card->number;
        $cardMemberNisn = $member?->nisn ?? $user->nisn ?? '-';
        $cardMemberExpired = $card->end_date->translatedFormat('d F Y');

        $barcodeGenerator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $barcodePng = $barcodeGenerator->getBarcode($card->number, $barcodeGenerator::TYPE_CODE_128);
        $barcodeImage = 'data:image/png;base64,' . base64_encode($barcodePng);

        // Foto disimpan di storage/app/public/{path}. dompdf tidak bisa diandalkan
        // untuk fetch gambar lewat URL (asset()/Storage::url()) saat generate PDF,
        // jadi foto dikonversi ke base64 dulu, sama seperti barcode di atas.
        // Kalau siswa belum punya foto (atau file-nya hilang), biarkan null ->
        // di blade akan otomatis fallback ke inisial nama.
        $cardMemberPhoto = null;

        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            $photoPath = Storage::disk('public')->path($user->photo);
            $photoMime = mime_content_type($photoPath) ?: 'image/jpeg';
            $cardMemberPhoto = 'data:' . $photoMime . ';base64,' . base64_encode(file_get_contents($photoPath));
        }

        // Logo instansi (sama seperti yang dipakai di navbar aplikasi: public/logo/kemenag.png).
        // Sama alasannya dengan foto -> dikonversi ke base64 supaya pasti muncul di PDF.
        $cardLogoImage = null;
        $logoPath = public_path('logo/kemenag.png');

        if (file_exists($logoPath)) {
            $logoMime = mime_content_type($logoPath) ?: 'image/png';
            $cardLogoImage = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('cards.card', compact(
            'cardMemberName',
            'cardMemberNo',
            'cardMemberNisn',
            'cardMemberExpired',
            'cardMemberPhoto',
            'cardLogoImage',
            'barcodeImage'
        ));

        return $pdf->download('kartu-anggota-' . $card->number . '.pdf');
    }
}