<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Order::query()->with(['user', 'orderDetails.item'])->latest()->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $order = Order::create($request->validate([
            'id_user' => ['required', 'integer', 'exists:users,id'],
            'total_harga' => ['required', 'numeric', 'min:0'],
            'status_pembayaran' => ['sometimes', 'string', 'max:50'],
            'status_pesanan' => ['sometimes', 'string', 'max:50'],
        ]));

        return response()->json($order->load(['user', 'orderDetails.item']), 201);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $order->update($request->validate([
            'id_user' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'total_harga' => ['sometimes', 'required', 'numeric', 'min:0'],
            'status_pembayaran' => ['sometimes', 'required', 'string', 'max:50'],
            'status_pesanan' => ['sometimes', 'required', 'string', 'max:50'],
        ]));

        return response()->json($order->load(['user', 'orderDetails.item']));
    }

    public function destroy(Order $order): JsonResponse
    {
        $order->delete();

        return response()->json(status: 204);
    }
}
