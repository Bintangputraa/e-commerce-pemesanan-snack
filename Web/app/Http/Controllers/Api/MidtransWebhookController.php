<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JsonException;

class MidtransWebhookController extends Controller
{
    public function __construct(protected MidtransService $midtransService) {}

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        if (empty($payload)) {
            $rawContent = (string) $request->getContent();
            if ($rawContent !== '') {
                try {
                    $payload = json_decode($rawContent, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException) {
                    return response()->json(['message' => 'Payload tidak valid.'], 400);
                }
            }
        }

        if (empty($payload)) {
            return response()->json(['message' => 'Payload tidak valid.'], 400);
        }

        $notificationData = [
            'transaction_time' => $payload['transaction_time'] ?? null,
            'transaction_status' => $payload['transaction_status'] ?? null,
            'transaction_id' => $payload['transaction_id'] ?? null,
            'status_message' => $payload['status_message'] ?? null,
            'status_code' => $payload['status_code'] ?? null,
            'signature_key' => $payload['signature_key'] ?? null,
            'settlement_time' => $payload['settlement_time'] ?? null,
            'payment_type' => $payload['payment_type'] ?? null,
            'order_id' => $payload['order_id'] ?? null,
            'merchant_id' => $payload['merchant_id'] ?? null,
            'gross_amount' => $payload['gross_amount'] ?? null,
            'fraud_status' => $payload['fraud_status'] ?? null,
            'currency' => $payload['currency'] ?? null,
        ];

        $notification = null;
        try {
            $notification = Notification::query()->create($notificationData);
        } catch (\Throwable $e) {
            report($e);
        }

        if (! $this->midtransService->verifyNotification($payload)) {
            return response()->json([
                'message' => 'Signature tidak valid.',
                'notification_id' => $notification?->id,
            ], 403);
        }

        $order = Order::query()
            ->where('midtrans_order_id', $payload['order_id'] ?? null)
            ->orWhere('id_order', $payload['order_id'] ?? null)
            ->first();

        if (! $order) {
            return response()->json([
                'message' => 'Order tidak ditemukan.',
                'notification_id' => $notification?->id,
            ], 404);
        }

        $this->midtransService->applyNotification($order, $payload);

        return response()->json([
            'message' => 'Notifikasi Midtrans diproses.',
            'status' => $order->fresh()->status_pembayaran,
            'notification_id' => $notification?->id,
        ]);
    }
}
