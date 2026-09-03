<?php

use App\Models\Favorite;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated users can toggle item favorites', function () {
    $user = User::factory()->create();
    $item = Item::query()->create([
        'nama' => 'Keripik Favorit',
        'harga' => 12000,
        'kategori' => 'Gurih',
    ]);

    $this->actingAs($user)->post("/favorites/{$item->id}/toggle")->assertRedirect();
    expect(Favorite::query()->where('id_user', $user->id)->where('id_item', $item->id)->exists())->toBeTrue();

    $this->actingAs($user)->post("/favorites/{$item->id}/toggle")->assertRedirect();
    expect(Favorite::query()->where('id_user', $user->id)->where('id_item', $item->id)->exists())->toBeFalse();
});

test('customers can view their favorite menu items', function () {
    $user = User::factory()->create();
    $item = Item::query()->create([
        'nama' => 'Camilan Pilihan',
        'harga' => 15000,
        'kategori' => 'Manis',
    ]);
    Favorite::query()->create(['id_user' => $user->id, 'id_item' => $item->id]);

    $this->actingAs($user)
        ->get('/orders/favorites')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('orders/Favorites')
            ->where('favorites.0.item.nama', 'Camilan Pilihan'));
});
