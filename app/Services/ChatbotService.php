<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Member;
use App\Support\LibrarySetting;
use Carbon\Carbon;

class ChatbotService
{
    /**
     * Daftar intent -> keyword "kuat" (aman untuk exact substring match).
     * Dipisah dari kata-kata generik supaya tidak salah tangkap intent lain.
     */
    private array $intents = [
        'salam' => ['halo', 'hai', 'hello', 'selamat pagi', 'selamat siang', 'selamat sore', 'assalamualaikum', 'hei', 'hi'],
        'jam_operasional' => ['jam buka', 'jam tutup', 'jam operasional', 'jadwal buka', 'kapan buka', 'kapan tutup', 'hari apa saja', 'buka jam berapa'],
        'aturan_pinjam' => ['aturan pinjam', 'syarat pinjam', 'cara pinjam', 'prosedur peminjaman', 'ketentuan pinjam', 'langkah pinjam', 'cara meminjam'],
        'denda' => ['denda', 'terlambat', 'telat kembalikan', 'sanksi keterlambatan', 'berapa denda', 'kena denda', 'keterlambatan'],
        'cara_perpanjang' => ['perpanjang', 'perpanjangan', 'extend pinjaman', 'tambah waktu pinjam', 'tambah hari pinjam'],
        'buku_saya' => ['buku saya', 'pinjaman saya', 'punya saya', 'kapan kembali buku saya', 'jatuh tempo saya', 'saya pinjam apa', 'status pinjaman saya'],
        'profil_saya' => ['profil saya', 'data saya', 'akun saya', 'siapa saya', 'info akun saya', 'nisn saya', 'kelas saya'],
        'daftar_buku' => ['daftar buku', 'list buku', 'buku apa saja', 'koleksi apa', 'semua buku', 'buku yang ada', 'rekomendasikan buku', 'rekomendasi buku'],
        'jumlah_koleksi' => ['berapa buku', 'total buku', 'jumlah buku', 'berapa koleksi', 'banyak buku', 'jumlah koleksi'],
        'cara_daftar' => ['daftar anggota', 'jadi anggota', 'pendaftaran anggota', 'cara daftar anggota', 'belum jadi anggota', 'mau daftar anggota', 'mendaftar anggota', 'registrasi anggota'],
        'cek_buku' => ['ada buku', 'cari buku', 'buku tersedia', 'stok buku', 'cek buku', 'nyari buku', 'ada tidak buku', 'ada gak buku', 'buku tentang'],
        'cara_ebook' => ['ebook', 'e-book', 'buku digital', 'baca online', 'baca buku digital', 'akses ebook'],
        'fitur_notifikasi' => ['notifikasi', 'lonceng', 'pemberitahuan', 'notif', 'pengingat'],
        'kontak_petugas' => ['hubungi petugas', 'kontak petugas', 'bantuan lain', 'butuh bantuan', 'komplain', 'tanya petugas'],
    ];

    private function maxTypoDistance(int $wordLength): int
    {
        return match (true) {
            $wordLength <= 4 => 0,
            $wordLength <= 7 => 1,
            default => 2,
        };
    }

    public function processMessage(string $message, ?Member $member = null): array
    {
        $intent = $this->detectIntent($message);

        if ($intent === 'fallback') {
            return $this->withGreeting([
                'intent' => 'fallback',
                'reply' => "Maaf, aku belum paham pertanyaanmu 🙏\nCoba tanyakan seputar: jam buka, cara pinjam, denda, cari buku, e-book, buku saya, atau hubungi petugas.",
            ], $member);
        }

        $result = [
            'intent' => $intent,
            'reply' => match ($intent) {
                'salam' => $this->sapaan($member),
                'jam_operasional' => "Perpustakaan kami buka setiap:\n📅 Senin - Jumat: 07.30 - 14.00 WITA\n🚫 Sabtu & Minggu: Libur\nJangan sampai ketinggalan ya!",
                'aturan_pinjam' => "Cara meminjam buku fisik di perpustakaan:\n1. Buka aplikasi dan pilih buku yang diinginkan atau lakukan scan barcode pada buku\n2. Isi data peminjaman melalui sistem/aplikasi\n3. Setelah berhasil, datangi petugas untuk mengambil buku fisiknya\n4. Batas waktu pinjam adalah 7 hari.\n\n📱 Untuk meminjam/membaca buku digital (E-Book):\n1. Login ke aplikasi menggunakan akun/identitas kamu\n2. Masuk ke menu E-Book\n3. Pilih dan baca buku secara online kapan saja!",
                'denda' => $this->dendaInfo($member),
                'cek_buku' => $this->searchBook($message),
                'daftar_buku' => $this->listBooks(),
                'jumlah_koleksi' => $this->countBooks(),
                'cara_daftar' => "Cara mendaftar sebagai anggota perpustakaan:\n1. Hubungi petugas perpustakaan sekolah\n2. Isi data nama, NISN, kelas, dan email jika ada\n3. Petugas akan memasukkan data anggota\n4. Setelah aktif, kamu bisa mulai meminjam buku\nSampai jumpa di perpustakaan! 📚",
                'cara_perpanjang' => "Untuk perpanjangan buku:\n1. Ajukan perpanjangan melalui aplikasi atau datang langsung sebelum tanggal jatuh tempo\n2. Masa pinjam akan diperpanjang 7 hari\n⚠️ Buku yang sudah terlambat tidak bisa diperpanjang, harus dikembalikan dulu.",
                'buku_saya' => $this->myLoans($member),
                'profil_saya' => $this->myProfile($member),
                'cara_ebook' => "Cara mengakses E-Book:\n1. Login ke akun kamu menggunakan identitas/akun siswa\n2. Buka menu \"E-Book\" di sidebar aplikasi\n3. Pilih buku digital yang ingin dibaca\n4. Klik \"Baca\" untuk membaca langsung secara digital\nSelamat membaca! 📱📚",
                'fitur_notifikasi' => "Fitur notifikasi 🔔 ada di ikon lonceng pada navbar atas.\nKamu akan otomatis dapat notifikasi kalau:\n- Buku kamu akan jatuh tempo besok\n- Buku kamu sudah terlambat dikembalikan\nCek berkala ya supaya tidak lupa!",
                'kontak_petugas' => "Butuh bantuan lebih lanjut? Silakan datang langsung ke perpustakaan sekolah pada jam operasional, atau hubungi petugas perpustakaan MTsN 3 Bone. 😊",
            },
        ];

        return $this->withGreeting($result, $member);
    }

    /**
     * Tambahkan sapaan nama di awal balasan (kecuali intent 'salam',
     * yang sudah punya sapaannya sendiri supaya tidak dobel).
     */
    private function withGreeting(array $result, ?Member $member): array
    {
        if (($result['intent'] ?? null) === 'salam') {
            return $result;
        }

        if (! $member) {
            return $result;
        }

        $namaDepan = explode(' ', trim($member->nama))[0];
        $result['reply'] = "Hai {$namaDepan}! 👋\n\n" . $result['reply'];

        return $result;
    }

    private function sapaan(?Member $member): string
    {
        if ($member) {
            $namaDepan = explode(' ', trim($member->nama))[0];

            return "Halo, {$namaDepan}! 👋 Selamat datang kembali di Perpustakaan MTsN 3 Bone 📚\nAda yang bisa saya bantu hari ini?";
        }

        return "Halo! Selamat datang di Perpustakaan MTsN 3 Bone 📚\nAda yang bisa saya bantu hari ini?";
    }

    /**
     * Info denda: umum (tarif) + personal (rincian denda milik member yang sedang login).
     */
    private function dendaInfo(?Member $member): string
    {
        $rate = LibrarySetting::lateFeePerDay();
        $contoh5Hari = $rate * 5;

        $generic = "Info denda keterlambatan:\n💰 Rp" . number_format($rate, 0, ',', '.') . " per hari per buku\nContoh: terlambat 5 hari = Rp" . number_format($contoh5Hari, 0, ',', '.') . "\nYuk kembalikan buku tepat waktu agar tidak kena denda! 😊";

        if (! $member) {
            return $generic . "\n\nMau cek denda kamu sendiri? Login dulu sebagai anggota ya 😊";
        }

        $telat = $member->loans()
            ->where('status', 'terlambat')
            ->with('book')
            ->get();

        $riwayatDibayar = $member->loans()
            ->where('status', 'dikembalikan')
            ->where('denda', '>', 0)
            ->with('book')
            ->orderByDesc('tgl_dikembalikan')
            ->take(5)
            ->get();

        if ($telat->isEmpty() && $riwayatDibayar->isEmpty()) {
            return $generic . "\n\n✅ Kabar baik, kamu tidak punya tunggakan denda saat ini.";
        }

        $reply = "Berikut rincian denda kamu ({$member->kelas}):\n\n";
        $totalEstimasi = 0;

        if ($telat->isNotEmpty()) {
            $baris = $telat->map(function ($loan) use ($rate, &$totalEstimasi) {
                $hariTelat = max(0, Carbon::parse($loan->tgl_kembali)->startOfDay()->diffInDays(now()->startOfDay()));
                $estimasi = $hariTelat * $rate;
                $totalEstimasi += $estimasi;
                $judul = $loan->book?->judul ?? 'Buku tidak diketahui';

                return "⚠️ {$judul}\nTerlambat {$hariTelat} hari, estimasi denda: Rp" . number_format($estimasi, 0, ',', '.');
            })->implode("\n\n");

            $reply .= $baris . "\n\n💰 Total estimasi denda berjalan: Rp" . number_format($totalEstimasi, 0, ',', '.') . "\n\n";
        } else {
            $reply .= "✅ Tidak ada buku yang sedang terlambat saat ini.\n\n";
        }

        if ($riwayatDibayar->isNotEmpty()) {
            $riwayat = $riwayatDibayar->map(function ($loan) {
                $judul = $loan->book?->judul ?? 'Buku tidak diketahui';

                return "- {$judul}: Rp" . number_format($loan->denda, 0, ',', '.');
            })->implode("\n");

            $reply .= "🧾 Riwayat denda terakhir:\n{$riwayat}\n\n";
        }

        $reply .= "💡 Tarif denda: Rp" . number_format($rate, 0, ',', '.') . " per hari per buku.";

        return $reply;
    }

    private function myProfile(?Member $member): string
    {
        if (! $member) {
            return "Kamu belum login sebagai anggota. Silakan login dulu untuk melihat profilmu ya 😊";
        }

        return "Data anggota kamu:\n👤 Nama: {$member->nama}\n🏫 Kelas: {$member->kelas}\n🆔 NISN: {$member->nisn}" .
            ($member->email ? "\n📧 Email: {$member->email}" : '');
    }

    // =========================================================================
    // NORMALISASI & DETEKSI INTENT
    // =========================================================================

    public function normalizeText(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s-]/u', ' ', $text) ?? $text;

        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
    }

    public function detectIntent(string $message): string
    {
        $normalized = $this->normalizeText($message);

        // Urutkan dari keyword paling spesifik (kata terbanyak, lalu terpanjang)
        // supaya frasa spesifik dicek lebih dulu daripada kata umum.
        $pairs = [];
        foreach ($this->intents as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                $pairs[] = ['intent' => $intent, 'keyword' => $keyword];
            }
        }

        usort($pairs, function ($a, $b) {
            $wordsA = substr_count($a['keyword'], ' ') + 1;
            $wordsB = substr_count($b['keyword'], ' ') + 1;

            return $wordsB <=> $wordsA ?: mb_strlen($b['keyword']) <=> mb_strlen($a['keyword']);
        });

        foreach ($pairs as $pair) {
            if (str_contains($normalized, $pair['keyword'])) {
                return $pair['intent'];
            }
        }

        // Fallback: pencocokan toleran typo (levenshtein per kata)
        $messageWords = explode(' ', $normalized);
        $bestIntent = null;
        $bestScore = 0;

        foreach ($pairs as $pair) {
            $keywordWords = explode(' ', $pair['keyword']);
            $matchedWords = 0;

            foreach ($keywordWords as $keywordWord) {
                foreach ($messageWords as $messageWord) {
                    if ($messageWord === '') {
                        continue;
                    }

                    $distance = levenshtein($keywordWord, $messageWord);

                    if ($distance <= $this->maxTypoDistance(mb_strlen($keywordWord))) {
                        $matchedWords++;
                        break;
                    }
                }
            }

            if ($matchedWords === count($keywordWords) && $matchedWords > $bestScore) {
                $bestScore = $matchedWords;
                $bestIntent = $pair['intent'];
            }
        }

        return $bestIntent ?? 'fallback';
    }

    public function searchBook(string $message): string
    {
        $normalized = $this->normalizeText($message);
        $stopwords = ['ada', 'tidak', 'buku', 'cari', 'cek', 'apakah', 'yang', 'di', 'ke', 'dan', 'atau', 'untuk', 'tentang', 'dengan', 'ini', 'itu', 'ya', 'gak', 'dong', 'kah'];
        $words = array_filter(explode(' ', $normalized), fn ($word) => ! in_array($word, $stopwords, true));
        $keyword = trim(implode(' ', $words));

        if ($keyword === '') {
            $keyword = $normalized;
        }

        $books = Book::query()
            ->where('judul', 'like', "%{$keyword}%")
            ->orWhere('penulis', 'like', "%{$keyword}%")
            ->get();

        if ($books->isEmpty() && $keyword !== '') {
            $allBooks = Book::query()->get(['id', 'judul', 'penulis', 'kategori', 'tahun', 'tersedia', 'stok']);

            $books = $allBooks->filter(function (Book $book) use ($keyword) {
                similar_text(mb_strtolower($book->judul), $keyword, $percentJudul);
                similar_text(mb_strtolower((string) $book->penulis), $keyword, $percentPenulis);

                return $percentJudul >= 55 || $percentPenulis >= 55;
            })->values();
        }

        if ($books->isEmpty()) {
            return "Maaf, buku dengan kata kunci '{$keyword}' tidak ada dalam koleksi kami.\nCoba kata kunci lain atau tanya langsung ke petugas ya 😊";
        }

        return $books->take(5)->map(function (Book $book) {
            $status = $book->tersedia > 0
                ? "✅ Tersedia: {$book->tersedia} dari {$book->stok} eksemplar"
                : "❌ Sedang habis dipinjam";

            return "📚 {$book->judul} - {$book->penulis}\nKategori: {$book->kategori} | Tahun: {$book->tahun}\n{$status}";
        })->implode("\n\n");
    }

    private function myLoans(?Member $member): string
    {
        if (! $member) {
            return "Untuk melihat status pinjamanmu, silakan login dulu sebagai anggota perpustakaan ya 😊";
        }

        $loans = $member->loans()
            ->whereIn('status', ['dipinjam', 'terlambat'])
            ->with('book')
            ->orderBy('tgl_kembali')
            ->get();

        if ($loans->isEmpty()) {
            return "Kamu tidak sedang meminjam buku apapun saat ini. Yuk mampir ke perpustakaan untuk pinjam buku! 📚";
        }

        $lines = $loans->map(function ($loan) {
            $judul = $loan->book?->judul ?? 'Buku tidak diketahui';
            $tglKembali = $loan->tgl_kembali?->format('d/m/Y') ?? '-';
            $status = $loan->status === 'terlambat' ? '⚠️ Terlambat' : '📖 Dipinjam';

            return "{$status} - {$judul}\nJatuh tempo: {$tglKembali}";
        })->implode("\n\n");

        return "Berikut status pinjaman aktifmu:\n\n{$lines}";
    }

    private function listBooks(): string
    {
        $books = Book::orderBy('judul')->take(10)->get();

        if ($books->isEmpty()) {
            return 'Saat ini belum ada data buku di perpustakaan.';
        }

        $lines = $books->values()->map(function (Book $book, int $index) {
            return ($index + 1) . ". {$book->judul} - {$book->penulis} ({$book->tersedia} tersedia)";
        })->implode("\n");

        return "Beberapa koleksi buku kami:\n{$lines}\n📚 Total koleksi: " . Book::count() . ' judul buku' . "\nKunjungi perpustakaan untuk melihat koleksi lengkapnya!";
    }

    private function countBooks(): string
    {
        $totalJudul = Book::count();
        $totalTersedia = Book::sum('tersedia');

        return "Koleksi perpustakaan kami:\n📚 Total judul: {$totalJudul} buku\n✅ Tersedia saat ini: {$totalTersedia} eksemplar\nAyo kunjungi perpustakaan dan temukan buku favoritmu!";
    }
}