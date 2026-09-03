<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->orders()->with(['user', 'orderDetails.item'])->latest()->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'total_harga' => ['required', 'numeric', 'min:0'],
            'status_pembayaran' => ['sometimes', 'string', 'max:50'],
            'status_pesanan' => ['sometimes', 'string', 'max:50'],
        ]);
        $order = Order::create(['id_user' => $request->user()->id, ...$data]);

        return response()->json($order->load(['user', 'orderDetails.item']), 201);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->id_user === $request->user()->id, 404);
        $order->update($request->validate([
            'total_harga' => ['sometimes', 'required', 'numeric', 'min:0'],
            'status_pembayaran' => ['sometimes', 'required', 'string', 'max:50'],
            'status_pesanan' => ['sometimes', 'required', 'string', 'max:50'],
        ]));

        return response()->json($order->load(['user', 'orderDetails.item']));
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->id_user === $request->user()->id, 404);
        $order->delete();

        return response()->json(status: 204);
    }
}
