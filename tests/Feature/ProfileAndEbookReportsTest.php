<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\EbookReadingLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\put;

test('pengguna bisa memperbarui profil sendiri beserta foto', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'role' => User::ROLE_ADMINISTRATOR,
        'nik' => null,
    ]);

    actingAs($user);

    put(route('profile.update'), [
        'name' => 'Admin Baru',
        'email' => 'adminbaru@example.com',
        'phone' => '081234567890',
        'bio' => 'Administrator perpustakaan digital.',
        'nik' => '1234567890123456',
        'photo' => UploadedFile::fake()->image('profil.jpg'),
    ])->assertSessionHasNoErrors()
        ->assertRedirect();

    $user->refresh();

    expect($user->photo)->not->toBeNull();
    Storage::disk('public')->assertExists($user->photo);
    assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Admin Baru',
        'email' => 'adminbaru@example.com',
        'nik' => '1234567890123456',
    ]);
});

test('administrator bisa membuka preview dan export pdf laporan history ebook', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMINISTRATOR,
    ]);

    $reader = User::factory()->create([
        'role' => User::ROLE_SISWA,
        'name' => 'Siswa Laporan',
        'kelas' => 'VIII A',
    ]);

    $book = Book::create([
        'category_id' => Category::query()->firstOrCreate(['category_name' => 'Pelajaran'])->id,
        'judul' => 'Geografi Digital',
        'penulis' => 'Tim IPS',
        'tahun' => 2026,
        'kategori' => 'Pelajaran',
        'stok' => 1,
        'tersedia' => 1,
        'has_ebook' => true,
        'ebook_path' => 'ebook/geografi.pdf',
    ]);

    EbookReadingLog::create([
        'user_id' => $reader->id,
        'book_id' => $book->id,
        'opened_at' => now()->subHour(),
        'last_read_at' => now(),
        'duration_seconds' => 600,
        'device_type' => 'desktop',
        'last_page_read' => 9,
    ]);

    actingAs($admin);

    get(route('admin.ebooks.history.print', ['report_period' => 'monthly']))
        ->assertOk()
        ->assertSeeText('Laporan Riwayat Baca E-Book')
        ->assertSeeText('Siswa Laporan');

    get(route('admin.ebooks.history.export-pdf', ['report_period' => 'monthly']))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
