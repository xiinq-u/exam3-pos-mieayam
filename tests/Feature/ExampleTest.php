<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('home page redirects to the React application', function () {
    $response = $this->get('/');

    $response->assertRedirect('/react');
});

test('authenticated user is also redirected to the React application', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertRedirect('/react');
});

test('React application shell is served for client-side routes', function () {
    $this->get('/react')
        ->assertOk()
        ->assertSee('<div id="app"></div>', false)
        ->assertSee('<title>POS Mie Ayam</title>', false);

    $this->get('/react/products')
        ->assertOk()
        ->assertSee('<div id="app"></div>', false);
});
