<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        if (! $this->midtransService->verifyNotification($payload)) {
            return response()->json(['message' => 'Signature tidak valid.'], 403);
        }

        $order = Order::query()
            ->where('midtrans_order_id', $payload['order_id'] ?? null)
            ->orWhere('id_order', $payload['order_id'] ?? null)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order tidak ditemukan.'], 404);
        }

        $this->midtransService->applyNotification($order, $payload);

        return response()->json([
            'message' => 'Notifikasi Midtrans diproses.',
            'status' => $order->fresh()->status_pembayaran,
        ]);
    }
}
