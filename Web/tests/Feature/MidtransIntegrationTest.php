<?php

use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('checkout generates a snap token and stores Midtrans metadata', function () {
    $snap = Mockery::mock('alias:Midtrans\Snap');
    $snap->shouldReceive('createTransaction')
        ->once()
        ->andReturn((object) [
            'token' => 'demo-snap-token-1',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/demo-snap-token-1',
            'transaction_id' => 'demo-transaction-1',
        ]);

    $user = User::query()->create([
        'name' => 'Reni Snack',
        'email' => 'reni@example.com',
        'whatsapp' => '081234567890',
        'alamat' => 'Jl. Mawar No. 10',
        'password' => bcrypt('password123'),
        'role' => 'customer',
    ]);

    $item = Item::query()->create([
        'nama' => 'Keripik Balado',
        'deskripsi' => 'Camilan pedas',
        'harga' => 25000,
        'gambar' => 'snacks/balado.jpg',
        'kategori' => 'Pedas',
    ]);

    $response = $this->actingAs($user)
        ->withHeader('Accept', 'application/json')
        ->post('/checkout', [
            'alamat_pengiriman' => 'Jl. Mawar No. 10',
            'tanggal_pesan' => now()->addDay()->toDateString(),
            'kode_voucher' => 'CEMIL10',
            'items' => [[
                'id' => $item->id,
                'quantity' => 2,
                'catatan' => 'Pedas sedang',
            ]],
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('snap_token', 'demo-snap-token-1');

    $this->assertDatabaseHas('orders', [
        'id_user' => $user->id,
        'status_pembayaran' => 'pending',
        'snap_token' => 'demo-snap-token-1',
    ]);
});

test('midtrans webhook updates the payment status on an order', function () {
    $user = User::query()->create([
        'name' => 'Ayu Snack',
        'email' => 'ayu@example.com',
        'whatsapp' => '081234567891',
        'alamat' => 'Jl. Melati No. 5',
        'password' => bcrypt('password123'),
        'role' => 'customer',
    ]);

    $order = Order::query()->create([
        'id_user' => $user->id,
        'total_harga' => 25000,
        'status_pembayaran' => 'pending',
        'status_pesanan' => 'pending',
        'alamat_pengiriman' => 'Jl. Melati No. 5',
        'tanggal_pesan' => now()->addDay()->toDateString(),
        'midtrans_order_id' => 'SNACK-TEST-1',
    ]);

    $signature = hash('sha512', 'SNACK-TEST-1'.'200'.'25000'.config('midtrans.server_key'));

    $response = $this->postJson('/api/midtrans/notification', [
        'order_id' => 'SNACK-TEST-1',
        'status_code' => '200',
        'gross_amount' => '25000',
        'signature_key' => $signature,
        'transaction_status' => 'settlement',
        'payment_type' => 'qris',
        'transaction_id' => 'txn-123',
    ]);

    $response->assertOk()
        ->assertJsonPath('status', 'paid');

    $this->assertDatabaseHas('orders', [
        'id_order' => $order->id_order,
        'status_pembayaran' => 'paid',
        'transaction_status' => 'settlement',
        'payment_type' => 'qris',
    ]);
});
