<?php

use App\Models\Batch;
use App\Models\Product;
use App\Models\User;

it('allows an authenticated user to manage batches', function () {
    $user = User::factory()->create();
    $product = Product::create([
        'name' => 'Tomates bio',
        'category' => 'Légumes',
        'origin_country' => 'France',
        'producer_id' => $user->id,
        'unit' => 'kg',
        'verification_status' => 'verified',
    ]);

    $this->actingAs($user)
        ->post(route('admin.batches.store'), [
            'product_id' => $product->id,
            'lot_number' => 'LOT-TEST-001',
            'production_date' => '2026-09-15',
            'expiration_date' => '2026-10-15',
            'quantity' => '125.50',
            'unit' => 'kg',
            'carbon_footprint' => '8.25',
            'status' => 'active',
        ])
        ->assertRedirect(route('admin.batches.index'));

    $batch = Batch::where('lot_number', 'LOT-TEST-001')->firstOrFail();

    $this->actingAs($user)->get(route('admin.batches.show', $batch))->assertOk();

    $this->actingAs($user)
        ->put(route('admin.batches.update', $batch), [
            'product_id' => $product->id,
            'lot_number' => 'LOT-TEST-001-UPDATED',
            'production_date' => '2026-09-15',
            'expiration_date' => '2026-10-15',
            'quantity' => '100',
            'unit' => 'kg',
            'carbon_footprint' => '7',
            'status' => 'recalled',
        ])
        ->assertRedirect(route('admin.batches.index'));

    expect($batch->fresh()->status)->toBe('recalled');

    $this->actingAs($user)
        ->delete(route('admin.batches.destroy', $batch))
        ->assertRedirect(route('admin.batches.index'));

    $this->assertDatabaseMissing('batches', ['id' => $batch->id]);
});

it('validates expiration after production and positive quantity', function () {
    $user = User::factory()->create();
    $product = Product::create([
        'name' => 'Pommes',
        'category' => 'Fruits',
        'origin_country' => 'France',
        'producer_id' => $user->id,
        'unit' => 'kg',
        'verification_status' => 'verified',
    ]);

    $this->actingAs($user)
        ->from(route('admin.batches.create'))
        ->post(route('admin.batches.store'), [
            'product_id' => $product->id,
            'lot_number' => 'LOT-TEST-002',
            'production_date' => '2026-09-15',
            'expiration_date' => '2026-09-14',
            'quantity' => '0',
            'unit' => 'kg',
            'status' => 'active',
        ])
        ->assertRedirect(route('admin.batches.create'))
        ->assertSessionHasErrors(['expiration_date', 'quantity']);
});
