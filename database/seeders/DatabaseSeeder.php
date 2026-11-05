<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categoryMap = collect([
            'Pelajaran',
            'Fiksi',
            'Non-Fiksi',
            'Referensi',
            'Lainnya',
        ])->mapWithKeys(function (string $categoryName) {
            $category = Category::updateOrCreate(
                ['category_name' => $categoryName],
                ['category_name' => $categoryName]
            );

            return [$categoryName => $category->id];
        });

        User::updateOrCreate(
            ['email' => 'admin@perpustakaan.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role' => User::ROLE_ADMIN,
                'nisn' => null,
                'kelas' => null,
                'email_verified_at' => now(),
            ]
        );

        $students = [
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad@siswa.com',
                'password' => 'siswa123',
                'kelas' => '5A',
                'nisn' => '1234567890',
            ],
            [
                'name' => 'Siti Aisyah',
                'email' => 'siti@siswa.com',
                'password' => 'siswa123',
                'kelas' => '4B',
                'nisn' => '0987654321',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@siswa.com',
                'password' => 'siswa123',
                'kelas' => '6A',
                'nisn' => '1122334455',
            ],
        ];

        foreach ($students as $studentData) {
            $user = User::updateOrCreate(
                ['email' => $studentData['email']],
                [
                    'name' => $studentData['name'],
                    'password' => Hash::make($studentData['password']),
                    'role' => User::ROLE_SISWA,
                    'nisn' => $studentData['nisn'],
                    'kelas' => $studentData['kelas'],
                    'email_verified_at' => now(),
                ]
            );

            Member::updateOrCreate(
                ['nisn' => $studentData['nisn']],
                [
                    'user_id' => $user->id,
                    'nama' => $studentData['name'],
                    'kelas' => $studentData['kelas'],
                    'email' => $studentData['email'],
                ]
            );
        }

        $books = [
            ['judul' => 'Matematika Kelas 4', 'penulis' => 'Kemendikbud', 'tahun' => 2022, 'kategori' => 'Pelajaran', 'stok' => 5],
            ['judul' => 'Bahasa Indonesia Kelas 5', 'penulis' => 'Kemendikbud', 'tahun' => 2022, 'kategori' => 'Pelajaran', 'stok' => 4],
            ['judul' => 'IPA Kelas 6', 'penulis' => 'Kemendikbud', 'tahun' => 2023, 'kategori' => 'Pelajaran', 'stok' => 3],
            ['judul' => 'IPS Kelas 4', 'penulis' => 'Kemendikbud', 'tahun' => 2022, 'kategori' => 'Pelajaran', 'stok' => 4],
            ['judul' => 'PKN Kelas 5', 'penulis' => 'Kemendikbud', 'tahun' => 2021, 'kategori' => 'Pelajaran', 'stok' => 3],
            ['judul' => 'Bumi dan Manusia', 'penulis' => 'Pramoedya Ananta Toer', 'tahun' => 2005, 'kategori' => 'Fiksi', 'stok' => 2],
            ['judul' => 'Ensiklopedi Anak', 'penulis' => 'Tim Penulis', 'tahun' => 2020, 'kategori' => 'Referensi', 'stok' => 3],
            ['judul' => 'Kisah 25 Nabi', 'penulis' => 'Abdul Aziz', 'tahun' => 2019, 'kategori' => 'Non-Fiksi', 'stok' => 4],
            ['judul' => 'Dongeng Nusantara', 'penulis' => 'Kolektif', 'tahun' => 2018, 'kategori' => 'Fiksi', 'stok' => 5],
            ['judul' => 'Atlas Indonesia', 'penulis' => 'Gramedia', 'tahun' => 2021, 'kategori' => 'Referensi', 'stok' => 2],
        ];

        foreach ($books as $bookData) {
            Book::updateOrCreate(
                ['judul' => $bookData['judul']],
                [
                    'category_id' => $categoryMap[$bookData['kategori']],
                    'penulis' => $bookData['penulis'],
                    'tahun' => $bookData['tahun'],
                    'kategori' => $bookData['kategori'],
                    'stok' => $bookData['stok'],
                    'tersedia' => $bookData['stok'],
                ]
            );
        }
    }
}
