<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Promo; // Pastikan Model Promo sudah dibuat
use App\Models\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->orders()->with(['user', 'orderDetails.item'])->latest()->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'alamat_pengiriman' => ['required', 'string'],
            'kode_voucher' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        
        $cartItems = Cart::where('id_user', $user->id)->with('item')->get();
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Keranjang kosong'], 400);
        }

        $total_gross = 0;
        $item_details = [];

        foreach ($cartItems as $cart) {
            $price = $cart->item->harga;
            $qty = $cart->jumlah;
            $total_gross += ($price * $qty);

            $item_details[] = [
                'id' => 'ITEM-' . $cart->item->id,
                'price' => (int)$price,
                'quantity' => (int)$qty,
                'name' => $cart->item->nama,
            ];
        }

        if ($request->filled('kode_voucher')) {
            $promo = Promo::where('code', $request->kode_voucher)->first();
            
            if ($promo && $promo->isValid($total_gross)[0]) {
                
                $discountValue = ($promo->discount_type == 'percentage') 
                    ? ($total_gross * ($promo->discount_amount / 100)) 
                    : $promo->discount_amount;

                $item_details[] = [
                    'id' => 'VOUCHER-' . $promo->id,
                    'price' => -(int)$discountValue,
                    'quantity' => 1,
                    'name' => 'Diskon: ' . $promo->code,
                ];
                
                $total_gross -= $discountValue;
                
                $promo->increment('used');
            }
        }

        $order = DB::transaction(function () use ($user, $total_gross, $data, $cartItems) {
            $newOrder = Order::create([
                'id_user' => $user->id,
                'total_harga' => $total_gross,
                'alamat_pengiriman' => $data['alamat_pengiriman'],
                'status_pembayaran' => 'pending',
                'status_pesanan' => 'pending'
            ]);

            foreach ($cartItems as $cart) {
                OrderDetail::create([
                    'id_order' => $newOrder->id,
                    'id_item' => $cart->id_item,
                    'jumlah' => $cart->jumlah,
                    'harga_satuan' => $cart->item->harga,
                    'catatan' => $data['catatan'] ?? ''
                ]);
            }

            Cart::where('id_user', $user->id)->delete();

            return $newOrder;
        });

        $midtrans_params = [
            'transaction_details' => [
                'order_id' => 'ORD-' . $order->id . '-' . time(),
                'gross_amount' => (int)$total_gross,
            ],
            'item_details' => $item_details,
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($midtrans_params);

        return response()->json([
            'success' => true,
            'order' => $order->load(['user', 'orderDetails.item']),
            'snap_token' => $snapToken
        ], 201);
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