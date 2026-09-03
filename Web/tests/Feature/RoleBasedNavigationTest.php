<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('customers are redirected to order history from dashboard', function () {
    $user = User::factory()->create(['role' => UserRole::Customer]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('orders.history'));
});

test('admins are redirected to the admin dashboard', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('admin.dashboard'));
});

test('customers cannot access admin pages', function () {
    $user = User::factory()->create(['role' => UserRole::Customer]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

test('admins can access order and menu management pages', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($user)->get('/admin/orders')->assertSuccessful();
    $this->actingAs($user)->get('/admin/items')->assertSuccessful();
});
