<?php

use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('can\'t access home page (unauthenticated)', function () {
    $response = get(route('home'));

    $response->assertStatus(302);
    $response->assertRedirectToRoute('login');
});

test('admin diarahkan ke dashboard admin dari home', function (User $user) {
    actingAs($user);

    $response = get(route('home'));

    $response->assertStatus(302);
    $response->assertRedirectToRoute('dashboard');
})->with('user_admin');

test('siswa diarahkan ke dashboard siswa dari home', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_SISWA,
    ]);

    actingAs($user);

    $response = get(route('home'));

    $response->assertStatus(302);
    $response->assertRedirectToRoute('siswa.dashboard');
});
