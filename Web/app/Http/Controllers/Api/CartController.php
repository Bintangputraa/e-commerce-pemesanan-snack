<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->carts()->with(['user', 'item'])->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $cart = Cart::create([
            'id_user' => $request->user()->id,
            ...$request->validate([
                'id_item' => ['required', 'integer', 'exists:items,id'],
                'jumlah' => ['required', 'integer', 'min:1'],
            ]),
        ]);

        return response()->json($cart->load(['user', 'item']), 201);
    }

    public function update(Request $request, Cart $cart): JsonResponse
    {
        abort_unless($cart->id_user === $request->user()->id, 404);
        $cart->update($request->validate([
            'id_item' => ['sometimes', 'required', 'integer', 'exists:items,id'],
            'jumlah' => ['sometimes', 'required', 'integer', 'min:1'],
        ]));

        return response()->json($cart->load(['user', 'item']));
    }

    public function destroy(Cart $cart): JsonResponse
    {
        abort_unless($cart->id_user === request()->user()->id, 404);
        $cart->delete();

        return response()->json(status: 204);
    }
}
