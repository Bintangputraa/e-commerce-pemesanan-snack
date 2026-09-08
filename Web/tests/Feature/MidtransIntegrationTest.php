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

test('notification endpoint stores payload from midtrans request body', function () {
    $user = User::query()->create([
        'name' => 'Ayu Snack',
        'email' => 'ayu2@example.com',
        'whatsapp' => '081234567892',
        'alamat' => 'Jl. Melati No. 9',
        'password' => bcrypt('password123'),
        'role' => 'customer',
    ]);

    $payload = [
        'transaction_time' => '2023-11-15 18:45:13',
        'transaction_status' => 'settlement',
        'transaction_id' => '513f1f01-c9da-474c-9fc9-d5c64364b709',
        'status_message' => 'midtrans payment notification',
        'status_code' => '200',
        'signature_key' => 'eaef3687a6e34ddb2ee8b0c68e1994db42738ffeed7f0315bcf0cd90d90251334f54da1c3ea73e48cfa4a720614b61b4f5cbabf372a4b2cebdbaec344383c387',
        'settlement_time' => '2023-11-15 22:45:13',
        'payment_type' => 'gopay',
        'order_id' => 'payment_notif_test_G959056152_25c09930-adec-4f30-b498-58038901d125',
        'merchant_id' => 'G959056152',
        'gross_amount' => '105000.00',
        'fraud_status' => 'accept',
        'currency' => 'IDR',
    ];

    $response = $this->actingAs($user)
        ->withHeader('Accept', 'application/json')
        ->postJson('/api/notifications', $payload);

    $response->assertStatus(201)
        ->assertJsonPath('transaction_status', 'settlement')
        ->assertJsonPath('order_id', $payload['order_id']);

    $this->assertDatabaseHas('notifications', [
        'id_user' => $user->id,
        'transaction_status' => 'settlement',
        'order_id' => $payload['order_id'],
        'payment_type' => 'gopay',
        'merchant_id' => 'G959056152',
    ]);
});
