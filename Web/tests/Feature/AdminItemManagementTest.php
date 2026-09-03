<?php

use App\Enums\UserRole;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can add a snack menu item', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/admin/items', [
        'nama' => 'Basreng Daun Jeruk',
        'deskripsi' => 'Renyah dan gurih',
        'harga' => 24000,
        'gambar' => 'https://example.com/basreng.jpg',
        'kategori' => 'Gurih',
    ]);

    $response->assertRedirect(route('admin.items'));
    $this->assertDatabaseHas('items', [
        'nama' => 'Basreng Daun Jeruk',
        'harga' => 24000,
    ]);
});

test('customer cannot add a snack menu item', function () {
    $customer = User::factory()->create(['role' => UserRole::Customer]);

    $this->actingAs($customer)->post('/admin/items', [
        'nama' => 'Tidak boleh',
        'harga' => 1000,
        'kategori' => 'Lainnya',
    ])->assertForbidden();

    expect(Item::query()->where('nama', 'Tidak boleh')->exists())->toBeFalse();
});

test('admin can update and delete a snack menu item', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $item = Item::query()->create([
        'nama' => 'Lama',
        'harga' => 10000,
        'kategori' => 'Gurih',
    ]);

    $this->actingAs($admin)->put("/admin/items/{$item->id}", [
        'nama' => 'Baru',
        'harga' => 15000,
        'kategori' => 'Manis',
    ])->assertRedirect(route('admin.items'));

    $this->assertDatabaseHas('items', ['id' => $item->id, 'nama' => 'Baru']);

    $this->actingAs($admin)->delete("/admin/items/{$item->id}")
        ->assertRedirect(route('admin.items'));

    $this->assertDatabaseMissing('items', ['id' => $item->id]);
});
