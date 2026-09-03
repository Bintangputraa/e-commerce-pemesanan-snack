<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\URL;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = config('midtrans.is_sanitized', true);
        Config::$is3ds = config('midtrans.is_3ds', true);
    }

    public function createTransaction(Order $order): array
    {
        $orderId = $order->midtrans_order_id ?: sprintf('SNACK-%s-%d', $order->id_order, time());
        $payload = $this->buildSnapPayload($order, $orderId);

        if (blank(config('midtrans.server_key'))) {
            throw new \RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
        }

        $response = Snap::createTransaction($payload);
        $token = data_get($response, 'token');

        if (blank($token)) {
            throw new \RuntimeException('Midtrans tidak mengembalikan Snap token.');
        }

        return [
            'order_id' => $orderId,
            'token' => $token,
            'redirect_url' => data_get($response, 'redirect_url'),
            'transaction_id' => data_get($response, 'transaction_id'),
        ];
    }

    public function verifyNotification(array $payload): bool
    {
        if (empty($payload['order_id']) || empty($payload['status_code']) || empty($payload['gross_amount'])) {
            return false;
        }

        $serverKey = config('midtrans.server_key');
        if (blank($serverKey)) {
            return false;
        }

        $expectedSignature = hash(
            'sha512',
            $payload['order_id'].
            $payload['status_code'].
            $payload['gross_amount'].
            $serverKey,
        );

        return hash_equals($expectedSignature, (string) ($payload['signature_key'] ?? ''));
    }

    public function synchronizeStatus(Order $order): Order
    {
        if (blank($order->midtrans_order_id)) {
            return $order;
        }

        $response = Transaction::status($order->midtrans_order_id);
        $payload = json_decode(json_encode($response), true);

        if (empty($payload['transaction_status'])) {
            throw new \RuntimeException('Midtrans tidak mengembalikan transaction_status.');
        }

        $this->applyNotification($order, $payload);

        return $order->fresh();
    }

    public function applyNotification(Order $order, array $payload): void
    {
        $transactionStatus = strtolower((string) ($payload['transaction_status'] ?? ''));
        if ($transactionStatus === '') {
            throw new \InvalidArgumentException('transaction_status Midtrans tidak tersedia.');
        }
        $paymentType = $payload['payment_type'] ?? $order->payment_type;
        $transactionId = $payload['transaction_id'] ?? $order->transaction_id;
        $normalizedStatus = match ($transactionStatus) {
            'capture', 'settlement' => 'paid',
            'pending' => 'pending',
            'deny', 'cancel', 'expire', 'failure' => 'failed',
            default => 'pending',
        };

        $order->update([
            'payment_type' => $paymentType,
            'transaction_id' => $transactionId,
            'transaction_status' => $transactionStatus,
            'status_pembayaran' => $normalizedStatus,
            'status_pesanan' => match ($normalizedStatus) {
                'paid' => 'processing',
                'failed' => 'cancelled',
                default => $order->status_pesanan ?? 'pending',
            },
        ]);
    }

    protected function buildSnapPayload(Order $order, string $orderId): array
    {
        $order->loadMissing(['orderDetails.item']);
        $itemDetails = $order->orderDetails->map(function ($detail): array {
            $itemName = $detail->item?->nama ?? 'Produk '.$detail->id_item;

            return [
                'id' => (string) $detail->id_item,
                'price' => (int) round((float) $detail->harga_satuan),
                'quantity' => (int) $detail->jumlah,
                'name' => $itemName,
            ];
        })->values()->all();

        if ((float) $order->diskon > 0) {
            $itemDetails[] = [
                'id' => 'discount',
                'price' => -((int) round((float) $order->diskon)),
                'quantity' => 1,
                'name' => 'Diskon',
            ];
        }

        $user = $order->user;

        return [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) round((float) $order->total_harga),
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $user?->name ?? 'Pelanggan',
                'email' => $user?->email ?? 'customer@example.com',
                'phone' => $user?->whatsapp ?? '081234567890',
                'shipping_address' => [
                    'first_name' => $user?->name ?? 'Pelanggan',
                    'address' => $order->alamat_pengiriman ?? 'Alamat belum diisi',
                ],
            ],
            'callbacks' => [
                'finish' => URL::route('orders.history', absolute: false),
            ],
            'expiry' => [
                'unit' => 'minutes',
                'duration' => 60,
            ],
            'enabled_payments' => ['gopay', 'shopeepay', 'qris', 'bank_transfer'],
            'credit_card' => [
                'secure' => true,
            ],
        ];
    }
}
