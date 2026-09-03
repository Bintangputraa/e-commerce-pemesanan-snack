<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can create update and delete users', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Customer Baru',
        'email' => 'customer@example.com',
        'whatsapp' => '08123456789',
        'alamat' => 'Jl. Snack No. 1',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'customer',
    ])->assertRedirect(route('admin.users'));

    $user = User::where('email', 'customer@example.com')->firstOrFail();

    $this->actingAs($admin)->put("/admin/users/{$user->id}", [
        'name' => 'Customer Updated',
        'whatsapp' => '08987654321',
        'alamat' => 'Alamat baru',
        'role' => 'admin',
    ])->assertRedirect(route('admin.users'));

    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Customer Updated', 'role' => 'admin']);

    $this->actingAs($admin)->delete("/admin/users/{$user->id}")
        ->assertRedirect(route('admin.users'));

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
