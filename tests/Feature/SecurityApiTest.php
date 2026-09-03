<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can revoke tokens from all devices', function () {
    $user = User::factory()->create();
    $user->createToken('first-device');
    $user->createToken('second-device');

    $this->actingAs($user, 'sanctum')->postJson('/api/logout-all')->assertOk();

    expect($user->fresh()->tokens()->count())->toBe(0);
});
