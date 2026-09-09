<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Promo; // Pastikan Model Promo sudah dibuat
use App\Models\Cart;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(protected MidtransService $midtransService) {}

    public function index(): JsonResponse
    {
        $data = Order::with('orderDetails.item')
            ->orderByDesc('tanggal_pesan')
            ->get();
        return response()->json($data);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'alamat_pengiriman' => ['required', 'string', 'max:1000'],
            'tanggal_pesan' => ['required', 'date', 'after_or_equal:tomorrow'],
            'kode_voucher' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.catatan' => ['nullable', 'string', 'max:500'],
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
        } else {
            $discountValue = 0;
        }

        $order = DB::transaction(function () use ($data, $discountValue, $request): Order {
            $items = Item::query()->whereIn('id', collect($data['items'])->pluck('id'))->get()->keyBy('id');
            $subtotal = 0;
            foreach ($data['items'] as $line) {
                $subtotal += $items[$line['id']]->harga * $line['quantity'];
            }

            $order = Order::create([
                'id_user' => $request->user()->id,
                'total_harga' => $subtotal - $discountValue,
                'status_pembayaran' => 'pending',
                'status_pesanan' => 'pending',
                'alamat_pengiriman' => $data['alamat_pengiriman'],
                'tanggal_pesan' => $data['tanggal_pesan'],
                'kode_voucher' => $data['kode_voucher'] ?? null,
                'diskon' => $discountValue,
            ]);

            foreach ($data['items'] as $line) {
                $order->orderDetails()->create([
                    'id_order' => $order->id_order,
                    'id_item' => $line['id'],
                    'jumlah' => $line['quantity'],
                    'harga_satuan' => $items[$line['id']]->harga,
                    'catatan' => $line['catatan'] ?? null,
                ]);
            }

            Cart::where('id_user', $request->user()->id)->delete();

            return $order;
        });

        try {
            $snapData = $this->midtransService->createTransaction($order);
        } catch (\Throwable $exception) {
            report($exception);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Pembayaran Midtrans gagal dibuat. '.$exception->getMessage(),
                ], 502);
            }

            return back()->withErrors([
                'payment' => 'Pembayaran Midtrans gagal dibuat. Silakan coba lagi.',
            ]);
        }

        // 4. Update data transaksi Midtrans ke Order
        $order->update([
            'midtrans_order_id' => $snapData['order_id'] ?? $order->id_order,
            'snap_token' => $snapData['token'] ?? null,
            'payment_url' => $snapData['redirect_url'] ?? null,
            'transaction_id' => $snapData['transaction_id'] ?? null,
            'transaction_status' => 'pending',
        ]);

        // 5. Kembalikan Response Sukses ke Aplikasi
        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat.',
            'data' => [
                'order_id' => $order->id_order,
                'total_pembayaran' => $order->total_harga,
                'snap_token' => $snapData['token'] ?? null,
                'payment_url' => $snapData['redirect_url'] ?? null,
                'order' => $order->fresh()->load('orderDetails.item'), // Me-load relasi untuk ditampilkan di aplikasi
            ]
        ], 201); // 201 Created
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->id_user === $request->user()->id, 404);
        $order->delete();

        return response()->json(status: 204);
    }
}