<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminUser = User::factory()->create();
});

it('can create a category as an authenticated admin', function () {
    Sanctum::actingAs($this->adminUser, ['*']);

    $response = $this->postJson(route('admin.categories.store'), [
        'name' => 'Electronics',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['message', 'category']);

    expect(Category::where('name', 'Electronics')->exists())->toBeTrue();
});

it('cannot create a category without authentication', function () {
    $response = $this->postJson(route('admin.categories.store'), [
        'name' => 'Books',
    ]);

    $response->assertStatus(401); // Unauthorized
});

it('can retrieve a single category as an authenticated user', function () {
    Sanctum::actingAs($this->adminUser, ['*']);

    $category = Category::factory()->create();

    $response = $this->getJson(route('admin.categories.show', $category->id));

    $response->assertStatus(200)
        ->assertJson([
            'id' => $category->id,
            'name' => $category->name,
        ]);
});

it('can update a category as an authenticated admin', function () {
    Sanctum::actingAs($this->adminUser, ['*']);

    $category = Category::factory()->create();

    $response = $this->putJson(route('admin.categories.update', $category->id), [
        'name' => 'Updated Category',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Category updated successfully.']);

    expect(Category::where('name', 'Updated Category')->exists())->toBeTrue();
});

it('can delete a category as an authenticated admin', function () {
    Sanctum::actingAs($this->adminUser, ['*']);

    $category = Category::factory()->create();

    $response = $this->deleteJson(route('admin.categories.destroy', $category->id));

    $response->assertStatus(200)
        ->assertJson(['message' => 'Category deleted successfully.']);

    expect(Category::where('id', $category->id)->exists())->toBeFalse();
});
