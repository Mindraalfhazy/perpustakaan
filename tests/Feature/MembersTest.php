<?php

use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

test('tidak bisa akses halaman anggota admin saat belum login', function () {
    $response = get(route('admin.members.index'));

    $response->assertStatus(302);
    $response->assertRedirectToRoute('login');
});

test('admin bisa akses halaman anggota admin', function (User $user) {
    actingAs($user);

    $response = get(route('admin.members.index'));

    $response->assertOk();
    $response->assertViewIs('members.index');
    $response->assertSeeText('Data Anggota');
})->with('user_admin');

test('admin bisa akses halaman tambah anggota', function (User $user) {
    actingAs($user);

    $response = get(route('admin.members.create'));

    $response->assertOk();
    $response->assertViewIs('members.create');
    $response->assertSeeText('Tambah Anggota Baru');
})->with('user_admin');

test('siswa diarahkan keluar dari halaman anggota admin', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_SISWA,
    ]);

    actingAs($user);

    $response = get(route('admin.members.index'));

    $response->assertStatus(302);
    $response->assertRedirectToRoute('siswa.dashboard');
});

test('admin bisa menambah anggota dan mengaitkannya ke user siswa yang cocok', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
    ]);

    $student = User::factory()->create([
        'role' => User::ROLE_SISWA,
        'name' => 'Rina Siswa',
        'email' => 'rina@siswa.com',
        'nisn' => '6677889900',
        'kelas' => '6B',
    ]);

    actingAs($user);

    $response = post(route('admin.members.store'), [
        'nama' => 'Rina Siswa',
        'nisn' => '6677889900',
        'kelas' => '6B',
        'email' => 'rina@siswa.com',
    ]);

    $response->assertRedirectToRoute('admin.members.index');
    assertDatabaseHas('members', [
        'nama' => 'Rina Siswa',
        'nisn' => '6677889900',
        'kelas' => '6B',
        'email' => 'rina@siswa.com',
        'user_id' => $student->id,
    ]);
});

test('admin bisa menambah anggota sekaligus membuat akun login siswa', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
    ]);

    actingAs($user);

    $response = post(route('admin.members.store'), [
        'nama' => 'Dina Anggota',
        'nisn' => '1010101010',
        'kelas' => '4B',
        'email' => 'dina@anggota.com',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ]);

    $response->assertRedirectToRoute('admin.members.index');

    $createdUser = User::where('email', 'dina@anggota.com')->firstOrFail();

    assertDatabaseHas('members', [
        'nama' => 'Dina Anggota',
        'nisn' => '1010101010',
        'kelas' => '4B',
        'email' => 'dina@anggota.com',
        'user_id' => $createdUser->id,
    ]);
    expect($createdUser->role)->toBe(User::ROLE_SISWA);
    expect(Hash::check('rahasia123', $createdUser->password))->toBeTrue();
});

test('admin bisa memperbarui data anggota', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
    ]);

    $member = Member::create([
        'nama' => 'Nama Lama',
        'nisn' => '5566778899',
        'kelas' => '5A',
        'email' => 'lama@anggota.com',
    ]);

    actingAs($user);

    $response = put(route('admin.members.update', $member), [
        'nama' => 'Nama Baru',
        'nisn' => '5566778899',
        'kelas' => '5B',
        'email' => 'baru@anggota.com',
    ]);

    $response->assertRedirectToRoute('admin.members.index');
    assertDatabaseHas('members', [
        'id' => $member->id,
        'nama' => 'Nama Baru',
        'kelas' => '5B',
        'email' => 'baru@anggota.com',
    ]);
});

test('admin bisa mengganti password akun login anggota saat edit data', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
    ]);

    $memberUser = User::factory()->create([
        'role' => User::ROLE_SISWA,
        'name' => 'Akun Lama',
        'email' => 'akun@anggota.com',
        'nisn' => '5656565656',
        'kelas' => '5A',
        'password' => 'passwordlama',
    ]);

    $member = Member::create([
        'user_id' => $memberUser->id,
        'nama' => 'Akun Lama',
        'nisn' => '5656565656',
        'kelas' => '5A',
        'email' => 'akun@anggota.com',
    ]);

    actingAs($user);

    $response = put(route('admin.members.update', $member), [
        'nama' => 'Akun Baru',
        'nisn' => '5656565656',
        'kelas' => '5B',
        'email' => 'akunbaru@anggota.com',
        'password' => 'passwordbaru123',
        'password_confirmation' => 'passwordbaru123',
    ]);

    $response->assertRedirectToRoute('admin.members.index');
    assertDatabaseHas('members', [
        'id' => $member->id,
        'user_id' => $memberUser->id,
        'nama' => 'Akun Baru',
        'kelas' => '5B',
        'email' => 'akunbaru@anggota.com',
    ]);
    expect($memberUser->fresh()->name)->toBe('Akun Baru');
    expect($memberUser->fresh()->email)->toBe('akunbaru@anggota.com');
    expect($memberUser->fresh()->kelas)->toBe('5B');
    expect(Hash::check('passwordbaru123', $memberUser->fresh()->password))->toBeTrue();
});

test('admin bisa menghapus anggota', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
    ]);

    $member = Member::create([
        'nama' => 'Hapus Anggota',
        'nisn' => '2233445566',
        'kelas' => '4A',
        'email' => 'hapus@anggota.com',
    ]);

    actingAs($user);

    $response = delete(route('admin.members.destroy', $member));

    $response->assertRedirectToRoute('admin.members.index');
    assertDatabaseMissing('members', [
        'id' => $member->id,
        'nisn' => '2233445566',
    ]);
});
