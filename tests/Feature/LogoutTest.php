<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can logout the current api token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $this->withToken($token)->postJson('/api/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Logged out successfully.');

    expect($user->fresh()->tokens()->count())->toBe(0);
});

test('logout only revokes the token used by the current device', function () {
    $user = User::factory()->create();
    $currentToken = $user->createToken('current-device')->plainTextToken;
    $user->createToken('other-device');

    $this->withToken($currentToken)->postJson('/api/logout')->assertOk();

    expect($user->fresh()->tokens()->count())->toBe(1)
        ->and($user->fresh()->tokens()->first()->name)->toBe('other-device');
});

test('unauthenticated user cannot access api logout', function () {
    $this->postJson('/api/logout')->assertUnauthorized();
});
