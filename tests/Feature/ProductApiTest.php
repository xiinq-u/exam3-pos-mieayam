<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('owner can list products through api', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $category = Category::factory()->create(['name' => 'Mie']);
    Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Mie Ayam',
        'price' => 15000,
    ]);

    $response = $this->actingAs($owner, 'sanctum')->getJson('/api/products');

    $response->assertOk()
        ->assertJsonFragment(['name' => 'Mie Ayam']);
});

test('owner can create and deactivate a product through api', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $category = Category::factory()->create();

    $product = $this->actingAs($owner, 'sanctum')->postJson('/api/products', [
        'name' => 'Es Teh',
        'category_id' => $category->id,
        'price' => 5000,
    ])->assertCreated()->json('data');

    $this->actingAs($owner, 'sanctum')->patchJson("/api/products/{$product['id']}", ['is_available' => false])
        ->assertOk()->assertJsonPath('data.is_available', false);
});

test('owner can soft delete a product through api', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $product = Product::factory()->create();

    $this->actingAs($owner, 'sanctum')
        ->deleteJson("/api/products/{$product->id}")
        ->assertNoContent();

    $this->assertSoftDeleted($product);
});

test('product api accepts a valid image and rejects non-image upload', function () {
    if (! function_exists('imagecreatetruecolor')) {
        $this->markTestSkipped('GD extension is not installed.');
    }

    Storage::fake('public');
    $owner = User::factory()->create(['role' => 'owner']);
    $category = Category::factory()->create();

    $this->actingAs($owner, 'sanctum')->postJson('/api/products', [
        'name' => 'Mie Foto', 'category_id' => $category->id, 'price' => 15000,
        'image' => UploadedFile::fake()->image('mie.jpg'),
    ])->assertCreated();

    $this->actingAs($owner, 'sanctum')->postJson('/api/products', [
        'name' => 'Mie File', 'category_id' => $category->id, 'price' => 15000,
        'image' => UploadedFile::fake()->create('data.pdf', 100, 'application/pdf'),
    ])->assertUnprocessable()
        ->assertJsonPath(
            'errors.image.0',
            'Foto produk harus berupa gambar. Disarankan memakai WebP atau JPG, rasio 1:1, dan resolusi sekitar 600 x 600 piksel.',
        );
});
