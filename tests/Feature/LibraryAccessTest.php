<?php

use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('admin dapat membuka halaman laporan', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
    ]);

    actingAs($user);

    $response = get(route('admin.reports.index'));

    $response->assertOk();
    $response->assertViewIs('reports.index');
    $response->assertSeeText('Laporan');
});

test('pengguna login dapat membuka halaman chatbot', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_SISWA,
    ]);

    actingAs($user);

    $response = get(route('chatbot.index'));

    $response->assertOk();
    $response->assertViewIs('chatbot.index');
    $response->assertSeeText('Asisten Perpustakaan');
});

test('admin dapat membuka halaman modul utama', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
    ]);

    actingAs($user);

    $pages = [
        'dashboard' => 'dashboard',
        'admin.books.index' => 'books.index',
        'admin.members.index' => 'members.index',
        'admin.loans.index' => 'loans.index',
        'admin.reports.index' => 'reports.index',
    ];

    foreach ($pages as $routeName => $viewName) {
        get(route($routeName))
            ->assertOk()
            ->assertViewIs($viewName);
    }
});

test('siswa dapat membuka halaman modul utama', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_SISWA,
    ]);

    actingAs($user);

    $pages = [
        'siswa.dashboard' => 'siswa.dashboard',
        'siswa.books.index' => 'siswa.books',
        'siswa.loans.index' => 'siswa.loans',
        'chatbot.index' => 'chatbot.index',
    ];

    foreach ($pages as $routeName => $viewName) {
        get(route($routeName))
            ->assertOk()
            ->assertViewIs($viewName);
    }
});
