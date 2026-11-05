<?php

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

test('tidak bisa akses halaman peminjaman admin saat belum login', function () {
    $response = get(route('admin.loans.index'));

    $response->assertStatus(302);
    $response->assertRedirectToRoute('login');
});

test('admin bisa akses halaman peminjaman admin', function (User $user) {
    actingAs($user);

    $response = get(route('admin.loans.index'));

    $response->assertOk();
    $response->assertViewIs('loans.index');
    $response->assertSeeText('Peminjaman');
})->with('user_admin');

test('admin bisa akses halaman tambah peminjaman', function (User $user) {
    actingAs($user);

    $response = get(route('admin.loans.create'));

    $response->assertOk();
    $response->assertViewIs('loans.create');
    $response->assertSeeText('Catat Peminjaman Baru');
})->with('user_admin');

test('siswa diarahkan keluar dari halaman peminjaman admin', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_SISWA,
    ]);

    actingAs($user);

    $response = get(route('admin.loans.index'));

    $response->assertStatus(302);
    $response->assertRedirectToRoute('siswa.dashboard');
});

test('admin bisa mencatat peminjaman dan stok buku berkurang', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
    ]);

    $member = Member::query()->firstOrFail();
    $book = Book::query()->where('tersedia', '>', 0)->firstOrFail();
    $stokAwal = $book->tersedia;

    actingAs($user);

    $response = post(route('admin.loans.store'), [
        'member_id' => $member->id,
        'book_id' => $book->id,
        'tgl_pinjam' => now()->toDateString(),
        'tgl_kembali' => now()->addDays(7)->toDateString(),
    ]);

    $response->assertRedirectToRoute('admin.loans.index');
    assertDatabaseHas('loans', [
        'member_id' => $member->id,
        'book_id' => $book->id,
        'status' => 'dipinjam',
        'denda' => 0,
    ]);
    expect($book->fresh()->tersedia)->toBe($stokAwal - 1);
});

test('admin tidak bisa mencatat peminjaman saat stok buku habis', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
    ]);

    $member = Member::query()->firstOrFail();
    $book = Book::query()->create([
        'category_id' => 1,
        'judul' => 'Buku Habis',
        'penulis' => 'Petugas',
        'tahun' => 2024,
        'kategori' => 'Lainnya',
        'stok' => 1,
        'tersedia' => 0,
    ]);

    actingAs($user);

    $response = post(route('admin.loans.store'), [
        'member_id' => $member->id,
        'book_id' => $book->id,
        'tgl_pinjam' => now()->toDateString(),
        'tgl_kembali' => now()->addDays(7)->toDateString(),
    ]);

    $response->assertSessionHasErrors('book_id');
    expect(Loan::where('book_id', $book->id)->count())->toBe(0);
});

test('admin bisa memproses pengembalian dan denda dihitung', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
    ]);

    $member = Member::query()->firstOrFail();
    $book = Book::query()->where('tersedia', '>', 0)->firstOrFail();
    $book->update(['tersedia' => 0]);

    $loan = Loan::create([
        'member_id' => $member->id,
        'book_id' => $book->id,
        'tgl_pinjam' => now()->subDays(10)->toDateString(),
        'tgl_kembali' => now()->subDays(2)->toDateString(),
        'status' => 'dipinjam',
        'denda' => 0,
    ]);

    actingAs($user);

    $response = post(route('admin.loans.return', $loan));

    $response->assertRedirectToRoute('admin.loans.index');
    assertDatabaseHas('loans', [
        'id' => $loan->id,
        'status' => 'dikembalikan',
        'denda' => 2000,
    ]);
    expect($book->fresh()->tersedia)->toBe(1);
});

test('admin bisa memperbarui data peminjaman dan stok buku ikut menyesuaikan', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
    ]);

    $memberLama = Member::query()->firstOrFail();
    $memberBaru = Member::query()->whereKeyNot($memberLama->id)->firstOrFail();
    $bookLama = Book::query()->where('tersedia', '>', 0)->firstOrFail();
    $bookBaru = Book::query()
        ->whereKeyNot($bookLama->id)
        ->where('tersedia', '>', 0)
        ->firstOrFail();

    $stokAwalBookLama = $bookLama->tersedia;
    $stokAwalBookBaru = $bookBaru->tersedia;

    $loan = Loan::create([
        'member_id' => $memberLama->id,
        'book_id' => $bookLama->id,
        'tgl_pinjam' => now()->toDateString(),
        'tgl_kembali' => now()->addDays(7)->toDateString(),
        'status' => 'dipinjam',
        'denda' => 0,
    ]);

    $bookLama->decrement('tersedia');

    actingAs($user);

    $response = put(route('admin.loans.update', $loan), [
        'member_id' => $memberBaru->id,
        'book_id' => $bookBaru->id,
        'tgl_pinjam' => now()->addDay()->toDateString(),
        'tgl_kembali' => now()->addDays(10)->toDateString(),
    ]);

    $response->assertRedirectToRoute('admin.loans.index');
    assertDatabaseHas('loans', [
        'id' => $loan->id,
        'member_id' => $memberBaru->id,
        'book_id' => $bookBaru->id,
        'status' => 'dipinjam',
    ]);
    expect($bookLama->fresh()->tersedia)->toBe($stokAwalBookLama);
    expect($bookBaru->fresh()->tersedia)->toBe($stokAwalBookBaru - 1);
});

test('admin tidak bisa memindahkan peminjaman ke buku yang stoknya habis', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
    ]);

    $member = Member::query()->firstOrFail();
    $bookLama = Book::query()->where('tersedia', '>', 0)->firstOrFail();
    $bookTujuan = Book::query()->create([
        'category_id' => 1,
        'judul' => 'Buku Tujuan Habis',
        'penulis' => 'Petugas',
        'tahun' => 2024,
        'kategori' => 'Lainnya',
        'stok' => 1,
        'tersedia' => 0,
    ]);

    $loan = Loan::create([
        'member_id' => $member->id,
        'book_id' => $bookLama->id,
        'tgl_pinjam' => now()->toDateString(),
        'tgl_kembali' => now()->addDays(7)->toDateString(),
        'status' => 'dipinjam',
        'denda' => 0,
    ]);

    $stokAwalBookLama = $bookLama->tersedia;
    $bookLama->decrement('tersedia');

    actingAs($user);

    $response = put(route('admin.loans.update', $loan), [
        'member_id' => $member->id,
        'book_id' => $bookTujuan->id,
        'tgl_pinjam' => now()->toDateString(),
        'tgl_kembali' => now()->addDays(7)->toDateString(),
    ]);

    $response->assertSessionHasErrors('book_id');
    assertDatabaseMissing('loans', [
        'id' => $loan->id,
        'book_id' => $bookTujuan->id,
    ]);
    expect($bookLama->fresh()->tersedia)->toBe($stokAwalBookLama - 1);
    expect($bookTujuan->fresh()->tersedia)->toBe(0);
});
