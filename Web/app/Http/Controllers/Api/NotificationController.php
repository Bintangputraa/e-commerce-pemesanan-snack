<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(protected MidtransService $midtransService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Notification::query()->latest('created_at');

        if ($request->user() && method_exists($request->user(), 'notifications')) {
            $query = $request->user()->notifications()->latest('created_at');
        }

        return response()->json($query->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (empty($payload)) {
            $raw = (string) $request->getContent();
            if ($raw !== '') {
                $payload = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            }
        }

        if (! empty($payload['transaction_status']) || ! empty($payload['order_id'])) {
            if (! $this->midtransService->verifyNotification($payload)) {
                return response()->json(['message' => 'Signature tidak valid.'], 403);
            }

            $order = Order::query()
                ->where('midtrans_order_id', $payload['order_id'] ?? null)
                ->orWhere('id_order', $payload['order_id'] ?? null)
                ->first();

            if ($order) {
                $this->midtransService->applyNotification($order, $payload);
            }
        }

        $data = $request->validate([
            'transaction_time' => ['sometimes', 'nullable', 'date_format:Y-m-d H:i:s'],
            'transaction_status' => ['sometimes', 'nullable', 'string', 'max:50'],
            'transaction_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status_message' => ['sometimes', 'nullable', 'string'],
            'status_code' => ['sometimes', 'nullable', 'string', 'max:10'],
            'signature_key' => ['sometimes', 'nullable', 'string'],
            'settlement_time' => ['sometimes', 'nullable', 'date_format:Y-m-d H:i:s'],
            'payment_type' => ['sometimes', 'nullable', 'string', 'max:50'],
            'order_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'merchant_id' => ['sometimes', 'nullable', 'string', 'max:100'],
            'gross_amount' => ['sometimes', 'nullable', 'numeric'],
            'fraud_status' => ['sometimes', 'nullable', 'string', 'max:50'],
            'currency' => ['sometimes', 'nullable', 'string', 'max:10'],
            'judul' => ['sometimes', 'nullable', 'string', 'max:255'],
            'pesan' => ['sometimes', 'nullable', 'string'],
            'status_baca' => ['sometimes', 'nullable', 'boolean'],
        ]);

        $notification = Notification::create($data);

        return response()->json($notification, 201);
    }

    public function update(Request $request, Notification $notification): JsonResponse
    {
        if ($request->user() && property_exists($notification, 'id_user') && $notification->id_user !== null) {
            abort_unless($notification->id_user === $request->user()->id, 404);
        }

        $notification->update($request->validate([
            'transaction_time' => ['sometimes', 'nullable', 'date_format:Y-m-d H:i:s'],
            'transaction_status' => ['sometimes', 'nullable', 'string', 'max:50'],
            'transaction_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status_message' => ['sometimes', 'nullable', 'string'],
            'status_code' => ['sometimes', 'nullable', 'string', 'max:10'],
            'signature_key' => ['sometimes', 'nullable', 'string'],
            'settlement_time' => ['sometimes', 'nullable', 'date_format:Y-m-d H:i:s'],
            'payment_type' => ['sometimes', 'nullable', 'string', 'max:50'],
            'order_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'merchant_id' => ['sometimes', 'nullable', 'string', 'max:100'],
            'gross_amount' => ['sometimes', 'nullable', 'numeric'],
            'fraud_status' => ['sometimes', 'nullable', 'string', 'max:50'],
            'currency' => ['sometimes', 'nullable', 'string', 'max:10'],
            'judul' => ['sometimes', 'nullable', 'string', 'max:255'],
            'pesan' => ['sometimes', 'nullable', 'string'],
            'status_baca' => ['sometimes', 'nullable', 'boolean'],
        ]));

        return response()->json($notification);
    }

    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        if ($request->user() && property_exists($notification, 'id_user') && $notification->id_user !== null) {
            abort_unless($notification->id_user === $request->user()->id, 404);
        }

        $notification->delete();

        return response()->json(status: 204);
    }
}
