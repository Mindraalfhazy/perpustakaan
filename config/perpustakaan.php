<?php

return [
    /**
     * Lamanya peminjaman.
     */
    'borrow_days' => 3,

    /**
     * Besar denda keterlambatan per hari.
     */
    'late_price' => 2000,

    /**
     * Besar denda kehilangan per buku.
     */
    'book_price' => 50000,

    /**
     * Profil sekolah untuk kebutuhan branding dan cetak laporan.
     */
    'school' => [
        'name' => 'MTsN 3 BONE',
        'short_name' => 'MTsN 3 Bone',
        'npsn' => '40320058',
        'address' => 'JLN.POROS BONE-MAKASSAR LEPPANGENG',
        'village' => 'PATANGKAI',
        'district' => 'KEC. LAPPARIAJA',
        'city' => 'KAB. BONE',
        'province' => 'PROV. SULAWESI SELATAN',
        'status' => 'NEGERI',
        'education_form' => 'MTs',
        'education_level' => 'DIKDAS',
        'logo' => 'logo/kemenag.png',
        'library_name' => 'Perpustakaan Digital',
    ],
];
