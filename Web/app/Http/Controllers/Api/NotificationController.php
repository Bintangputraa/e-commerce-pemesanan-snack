<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->notifications()->with('user')->latest('created_at')->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_user' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'judul' => ['sometimes', 'nullable', 'string', 'max:255'],
            'pesan' => ['sometimes', 'nullable', 'string'],
            'status_baca' => ['sometimes', 'boolean'],
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
        ]);

        $userId = $request->input('id_user', $request->user()?->id);
        $data['id_user'] = $userId;

        if (empty($data['judul']) && ! empty($data['transaction_status'])) {
            $data['judul'] = 'Pembayaran '.strtoupper($data['transaction_status']);
        }

        if (empty($data['pesan']) && ! empty($data['status_message'])) {
            $data['pesan'] = $data['status_message'];
        }

        if (empty($data['pesan']) && ! empty($data['transaction_status'])) {
            $data['pesan'] = 'Status transaksi: '.strtoupper($data['transaction_status']);
        }

        $notification = Notification::create($data);

        return response()->json($notification->load('user'), 201);
    }

    public function update(Request $request, Notification $notification): JsonResponse
    {
        abort_unless($notification->id_user === $request->user()->id, 404);
        $notification->update($request->validate([
            'judul' => ['sometimes', 'nullable', 'string', 'max:255'],
            'pesan' => ['sometimes', 'nullable', 'string'],
            'status_baca' => ['sometimes', 'required', 'boolean'],
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
        ]));

        return response()->json($notification->load('user'));
    }

    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        abort_unless($notification->id_user === $request->user()->id, 404);
        $notification->delete();

        return response()->json(status: 204);
    }
}
