<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;

test('tidak bisa akses halaman buku admin saat belum login', function () {
    $response = get(route('admin.books.index'));

    $response->assertStatus(302);
    $response->assertRedirectToRoute('login');
});

test('admin bisa akses halaman buku admin', function (User $user) {
    actingAs($user);

    $response = get(route('admin.books.index'));

    $response->assertOk();
    $response->assertViewIs('books.index');
    $response->assertSeeText('Data Buku');
})->with('user_admin');

test('admin bisa akses halaman tambah buku', function (User $user) {
    actingAs($user);

    $response = get(route('admin.books.create'));

    $response->assertOk();
    $response->assertViewIs('books.create');
    $response->assertSeeText('Tambah Buku Baru');
})->with('user_admin');

test('siswa diarahkan keluar dari halaman buku admin', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_SISWA,
    ]);

    actingAs($user);

    $response = get(route('admin.books.index'));

    $response->assertStatus(302);
    $response->assertRedirectToRoute('siswa.dashboard');
});

test('admin bisa menambah buku baru', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
    ]);

    actingAs($user);

    $response = post(route('admin.books.store'), [
        'judul' => 'Cerita Sains Anak',
        'penulis' => 'Tim Edukasi',
        'tahun' => 2024,
        'kategori' => 'Referensi',
        'stok' => 7,
    ]);

    $category = Category::where('category_name', 'Referensi')->firstOrFail();

    $response->assertRedirectToRoute('admin.books.index');
    assertDatabaseHas('books', [
        'judul' => 'Cerita Sains Anak',
        'penulis' => 'Tim Edukasi',
        'tahun' => 2024,
        'kategori' => 'Referensi',
        'stok' => 7,
        'tersedia' => 7,
        'category_id' => $category->id,
    ]);
});

test('admin bisa memperbarui buku dan stok tersedia ikut tersesuaikan', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
    ]);

    $book = Book::create([
        'category_id' => Category::query()->firstOrCreate(['category_name' => 'Lainnya'])->id,
        'judul' => 'Buku Uji Lama',
        'penulis' => 'Penulis Lama',
        'tahun' => 2022,
        'kategori' => 'Lainnya',
        'stok' => 4,
        'tersedia' => 2,
    ]);

    actingAs($user);

    $response = put(route('admin.books.update', $book), [
        'judul' => 'Buku Uji Baru',
        'penulis' => 'Penulis Baru',
        'tahun' => 2025,
        'kategori' => 'Fiksi',
        'stok' => 6,
    ]);

    $category = Category::where('category_name', 'Fiksi')->firstOrFail();

    $response->assertRedirectToRoute('admin.books.index');
    assertDatabaseHas('books', [
        'id' => $book->id,
        'judul' => 'Buku Uji Baru',
        'penulis' => 'Penulis Baru',
        'tahun' => 2025,
        'kategori' => 'Fiksi',
        'stok' => 6,
        'tersedia' => 4,
        'category_id' => $category->id,
    ]);
});

test('admin bisa menghapus buku', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
    ]);

    $book = Book::create([
        'category_id' => Category::query()->firstOrCreate(['category_name' => 'Pelajaran'])->id,
        'judul' => 'Buku Hapus',
        'penulis' => 'Penulis Hapus',
        'tahun' => 2023,
        'kategori' => 'Pelajaran',
        'stok' => 2,
        'tersedia' => 2,
    ]);

    actingAs($user);

    $response = delete(route('admin.books.destroy', $book));

    $response->assertRedirectToRoute('admin.books.index');
    assertDatabaseMissing('books', [
        'id' => $book->id,
        'judul' => 'Buku Hapus',
    ]);
});
