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
        // 1. Validasi input dari Android
        $data = $request->validate([
            'address' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'voucher_code' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        
        // 2. Ambil data keranjang user (untuk keamanan, jangan percaya total_harga dari client)
        $cartItems = Cart::where('id_user', $user->id)->with('item')->get();
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Keranjang kosong'], 400);
        }

        $total_gross = 0;
        $item_details = [];

        // 3. Susun item_details dari produk asli
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

        // 4. LOGIC VOUCHER: Tambahkan Diskon sebagai item NEGATIF
        if ($request->filled('voucher_code')) {
            $promo = Promo::where('code', $request->voucher_code)->first();
            
            // Validasi promo (Menggunakan fungsi isValid yang kita buat sebelumnya)
            if ($promo && $promo->isValid($total_gross)[0]) {
                
                // Hitung nilai diskon
                $discountValue = ($promo->discount_type == 'percentage') 
                    ? ($total_gross * ($promo->discount_amount / 100)) 
                    : $promo->discount_amount;

                // Tambahkan sebagai baris baru di item_details Midtrans
                $item_details[] = [
                    'id' => 'VOUCHER-' . $promo->id,
                    'price' => -(int)$discountValue, // WAJIB NEGATIF agar memotong total
                    'quantity' => 1,
                    'name' => 'Diskon: ' . $promo->code,
                ];
                
                $total_gross -= $discountValue; // Update total akhir
                
                // Update quota used di database
                $promo->increment('used');
            }
        }

        // 5. Simpan Order ke Database
        $order = DB::transaction(function () use ($user, $total_gross, $data, $cartItems) {
            $newOrder = Order::create([
                'id_user' => $user->id,
                'total_harga' => $total_gross,
                'address' => $data['address'],
                'notes' => $data['notes'] ?? '',
                'status_pembayaran' => 'pending',
                'status_pesanan' => 'pending'
            ]);

            // Pindahkan data dari cart ke order_details
            foreach ($cartItems as $cart) {
                OrderDetail::create([
                    'id_order' => $newOrder->id,
                    'id_item' => $cart->id_item,
                    'quantity' => $cart->jumlah,
                    'price' => $cart->item->harga
                ]);
            }

            // Opsional: Hapus keranjang setelah order dibuat (atau tunggu callback bayar)
            // Cart::where('id_user', $user->id)->delete();

            return $newOrder;
        });

        // 6. Siapkan Parameter Midtrans
        $midtrans_params = [
            'transaction_details' => [
                'order_id' => 'ORD-' . $order->id . '-' . time(),
                'gross_amount' => (int)$total_gross,
            ],
            'item_details' => $item_details, // Variabel yang ditanyakan
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
        ];

        // 7. Generate Snap Token (Gunakan library Midtrans PHP)
        // $snapToken = \Midtrans\Snap::getSnapToken($midtrans_params);

        return response()->json([
            'success' => true,
            'order' => $order->load(['user', 'orderDetails.item']),
            // 'snap_token' => $snapToken // Kirim ini ke Android untuk buka halaman pembayaran
        ], 201);
    }

    // Fungsi update dan destroy tetap seperti sebelumnya...
}