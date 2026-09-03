<?php

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('storefront shares snack items from the database', function () {
    $item = Item::query()->create([
        'nama' => 'Keripik Singkong Balado',
        'deskripsi' => 'Camilan renyah',
        'harga' => 25000,
        'gambar' => 'snacks/keripik.jpg',
        'kategori' => 'Pedas',
    ]);

    $this->get('/')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->has('items', 1)
            ->where('items.0.id', $item->id)
            ->where('items.0.name', 'Keripik Singkong Balado')
            ->where('items.0.price', 25000)
            ->where('items.0.category', 'Pedas'));
});
