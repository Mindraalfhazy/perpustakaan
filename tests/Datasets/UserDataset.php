<?php

use App\Models\User;

dataset('user_admin', function () {
    return yield function () {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
    };
});

dataset('user_pustakawan', function () {
    return yield function () {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
    };
});
