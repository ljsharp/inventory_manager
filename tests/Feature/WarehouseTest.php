<?php

use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a warehouse', function () {
    $response = $this->postJson('/api/warehouses', [
        'name' => 'Test Warehouse',
        'location' => '123 Test Street',
        'contact_info' => 'test@example.com',
        'capacity' => 1000,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['message', 'warehouse']);

    $this->assertDatabaseHas('warehouses', ['name' => 'Test Warehouse']);
});

it('validates required fields when creating a warehouse', function () {
    $response = $this->postJson('/api/warehouses', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'location', 'capacity']);
});

it('can retrieve a list of warehouses', function () {
    Warehouse::factory()->count(3)->create();

    $response = $this->getJson('/api/warehouses');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

it('can retrieve a single warehouse', function () {
    $warehouse = Warehouse::factory()->create();

    $response = $this->getJson("/api/warehouses/{$warehouse->id}");

    $response->assertStatus(200)
        ->assertJsonFragment(['name' => $warehouse->name]);
});

it('can update a warehouse', function () {
    $warehouse = Warehouse::factory()->create();

    $response = $this->putJson("/api/warehouses/{$warehouse->id}", [
        'name' => 'Updated Warehouse',
        'location' => 'Updated Location',
        'contact_info' => 'updated@example.com',
        'capacity' => 5000,
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['name' => 'Updated Warehouse']);

    $this->assertDatabaseHas('warehouses', ['name' => 'Updated Warehouse']);
});

it('can delete a warehouse', function () {
    $warehouse = Warehouse::factory()->create();

    $response = $this->deleteJson("/api/warehouses/{$warehouse->id}");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Warehouse deleted successfully!']);

    $this->assertDatabaseMissing('warehouses', ['id' => $warehouse->id]);
});
