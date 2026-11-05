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
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;
use function Pest\Laravel\delete;

test('administrator bisa menambah buku beserta file ebook', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'role' => User::ROLE_ADMINISTRATOR,
    ]);

    actingAs($user);

    $response = post(route('admin.books.store'), [
        'judul' => 'Fisika Digital',
        'penulis' => 'Tim Laboratorium',
        'tahun' => 2025,
        'kategori' => 'Pelajaran',
        'stok' => 3,
        'ebook' => UploadedFile::fake()->create('fisika-digital.pdf', 1200, 'application/pdf'),
    ]);

    $book = Book::where('judul', 'Fisika Digital')->firstOrFail();

    $response->assertRedirectToRoute('admin.books.index');
    expect($book->has_ebook)->toBeTrue();
    expect($book->ebook_path)->not->toBeNull();
    Storage::disk('public')->assertExists($book->ebook_path);
});

test('administrator bisa mengganti lalu menghapus file ebook', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'role' => User::ROLE_ADMINISTRATOR,
    ]);

    $book = Book::create([
        'category_id' => Category::query()->firstOrCreate(['category_name' => 'Pelajaran'])->id,
        'judul' => 'Kimia Dasar',
        'penulis' => 'Guru IPA',
        'tahun' => 2024,
        'kategori' => 'Pelajaran',
        'stok' => 5,
        'tersedia' => 5,
    ]);

    actingAs($user);

    post(route('admin.books.upload-ebook', $book), [
        'ebook' => UploadedFile::fake()->create('kimia-v1.pdf', 1000, 'application/pdf'),
    ])->assertRedirectToRoute('admin.books.edit', $book);

    $book->refresh();
    $firstPath = $book->ebook_path;

    post(route('admin.books.upload-ebook', $book), [
        'ebook' => UploadedFile::fake()->create('kimia-v2.pdf', 1000, 'application/pdf'),
    ])->assertRedirectToRoute('admin.books.edit', $book);

    $book->refresh();

    expect($book->has_ebook)->toBeTrue();
    expect($book->ebook_path)->not->toBe($firstPath);
    Storage::disk('public')->assertMissing($firstPath);
    Storage::disk('public')->assertExists($book->ebook_path);

    delete(route('admin.books.destroy-ebook', $book))
        ->assertRedirectToRoute('admin.books.edit', $book);

    $book->refresh();

    assertDatabaseHas('books', [
        'id' => $book->id,
        'has_ebook' => 0,
        'ebook_path' => null,
    ]);
});

test('siswa bisa mengakses daftar ebook, reader, dan file pdf melalui controller', function () {
    Storage::fake('public');
    Storage::disk('public')->put('ebook/bahasa-indonesia.pdf', '%PDF-1.4 test pdf');

    $user = User::factory()->create([
        'role' => User::ROLE_SISWA,
    ]);

    $book = Book::create([
        'category_id' => Category::query()->firstOrCreate(['category_name' => 'Pelajaran'])->id,
        'judul' => 'Bahasa Indonesia Digital',
        'penulis' => 'Tim Bahasa',
        'tahun' => 2025,
        'kategori' => 'Pelajaran',
        'stok' => 2,
        'tersedia' => 2,
        'has_ebook' => true,
        'ebook_path' => 'ebook/bahasa-indonesia.pdf',
    ]);

    actingAs($user);

    get(route('ebooks.index'))
        ->assertOk()
        ->assertViewIs('siswa.ebooks.index')
        ->assertSeeText('Bahasa Indonesia Digital');

    get(route('ebooks.read', $book))
        ->assertOk()
        ->assertViewIs('siswa.ebooks.read')
        ->assertSeeText('Bahasa Indonesia Digital');

    assertDatabaseHas('ebook_reading_logs', [
        'user_id' => $user->id,
        'book_id' => $book->id,
    ]);

    get(route('ebooks.file', $book))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

test('siswa bisa menyimpan progres baca ebook ke log', function () {
    Storage::fake('public');
    Storage::disk('public')->put('ebook/progres.pdf', '%PDF-1.4 test pdf');

    $user = User::factory()->create([
        'role' => User::ROLE_SISWA,
    ]);

    $book = Book::create([
        'category_id' => Category::query()->firstOrCreate(['category_name' => 'Pelajaran'])->id,
        'judul' => 'Matematika Progres',
        'penulis' => 'Guru Matematika',
        'tahun' => 2025,
        'kategori' => 'Pelajaran',
        'stok' => 1,
        'tersedia' => 1,
        'has_ebook' => true,
        'ebook_path' => 'ebook/progres.pdf',
    ]);

    actingAs($user);

    get(route('ebooks.read', $book))->assertOk();

    $log = EbookReadingLog::query()
        ->where('user_id', $user->id)
        ->where('book_id', $book->id)
        ->latest('id')
        ->firstOrFail();

    postJson(route('ebooks.progress', [$book, $log]), [
        'page' => 7,
        'event' => 'page-change',
    ])->assertOk()
        ->assertJson([
            'status' => 'ok',
            'last_page_read' => 7,
        ]);

    assertDatabaseHas('ebook_reading_logs', [
        'id' => $log->id,
        'last_page_read' => 7,
    ]);
});

test('administrator bisa melihat history pembaca ebook', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMINISTRATOR,
    ]);

    $reader = User::factory()->create([
        'role' => User::ROLE_SISWA,
        'name' => 'Siswa Pembaca',
    ]);

    $book = Book::create([
        'category_id' => Category::query()->firstOrCreate(['category_name' => 'Pelajaran'])->id,
        'judul' => 'Sejarah Nusantara',
        'penulis' => 'Tim IPS',
        'tahun' => 2025,
        'kategori' => 'Pelajaran',
        'stok' => 2,
        'tersedia' => 2,
        'has_ebook' => true,
        'ebook_path' => 'ebook/sejarah.pdf',
    ]);

    EbookReadingLog::create([
        'user_id' => $reader->id,
        'book_id' => $book->id,
        'opened_at' => now()->subMinutes(10),
        'last_read_at' => now(),
        'duration_seconds' => 420,
        'device_type' => 'mobile',
        'last_page_read' => 12,
    ]);

    actingAs($user);

    get(route('admin.ebooks.history'))
        ->assertOk()
        ->assertSeeText('Siswa Pembaca')
        ->assertSeeText('Sejarah Nusantara')
        ->assertSeeText('History Pembaca E-Book');
});

test('administrator diarahkan keluar dari halaman ebook siswa', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMINISTRATOR,
    ]);

    actingAs($user);

    get(route('ebooks.index'))
        ->assertRedirectToRoute('dashboard');
});
