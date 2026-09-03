<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Cart::query()->with(['user', 'item'])->latest()->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $cart = Cart::create($request->validate([
            'id_user' => ['required', 'integer', 'exists:users,id'],
            'id_item' => ['required', 'integer', 'exists:items,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
        ]));

        return response()->json($cart->load(['user', 'item']), 201);
    }

    public function update(Request $request, Cart $cart): JsonResponse
    {
        $cart->update($request->validate([
            'id_user' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'id_item' => ['sometimes', 'required', 'integer', 'exists:items,id'],
            'jumlah' => ['sometimes', 'required', 'integer', 'min:1'],
        ]));

        return response()->json($cart->load(['user', 'item']));
    }

    public function destroy(Cart $cart): JsonResponse
    {
        $cart->delete();

        return response()->json(status: 204);
    }
}
