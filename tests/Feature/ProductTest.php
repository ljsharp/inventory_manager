<?php

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = Category::factory()->create();
    $this->adminUser = User::factory()->create(); // Create an admin user
});

it('can create a product as an authenticated admin', function () {
    Sanctum::actingAs($this->adminUser, ['*']);

    $response = $this->postJson(route('admin.products.store'), [
        'name' => 'Test Product',
        'description' => 'This is a test product.',
        'category_id' => $this->category->id,
        'price' => 99.99,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['message', 'product']);

    expect(Product::where('name', 'Test Product')->exists())->toBeTrue();
});

it('cannot create a product without authentication', function () {
    $response = $this->postJson(route('admin.products.store'), [
        'name' => 'Test Product',
        'description' => 'This is a test product.',
        'category_id' => $this->category->id,
        'price' => 99.99,
    ]);

    $response->assertStatus(401);
});

it('can retrieve a single product as an authenticated user', function () {
    Sanctum::actingAs($this->adminUser, ['*']);

    $product = Product::factory()->create();

    $response = $this->getJson(route('admin.products.show', $product->id));

    $response->assertStatus(200)
        ->assertJson([
            'id' => $product->id,
            'name' => $product->name,
        ]);
});

it('can update a product as an authenticated admin', function () {
    Sanctum::actingAs($this->adminUser, ['*']);

    $product = Product::factory()->create();

    $response = $this->putJson(route('admin.products.update', $product->id), [
        'name' => 'Updated Product',
        'description' => 'Updated description.',
        'category_id' => $this->category->id,
        'price' => 79.99,
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Product updated successfully.']);

    expect(Product::where('name', 'Updated Product')->exists())->toBeTrue();
});

it('can delete a product as an authenticated admin', function () {
    Sanctum::actingAs($this->adminUser, ['*']);

    $product = Product::factory()->create();

    $response = $this->deleteJson(route('admin.products.destroy', $product->id));

    $response->assertStatus(200)
        ->assertJson(['message' => 'Product deleted successfully.']);

    expect(Product::where('id', $product->id)->exists())->toBeFalse();
});
