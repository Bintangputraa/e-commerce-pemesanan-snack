<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(protected MidtransService $midtransService) {}

    public function show(Request $request): Response
    {
        $cart = collect(json_decode($request->string('cart')->toString(), true) ?: [])
            ->mapWithKeys(fn (mixed $quantity, string $id): array => [(int) $id => max(1, (int) $quantity)]);
        $items = Item::query()->whereIn('id', $cart->keys())->get()->map(fn (Item $item): array => [
            'id' => $item->id,
            'name' => $item->nama,
            'price' => (float) $item->harga,
            'quantity' => $cart->get($item->id, 1),
        ])->values();

        return Inertia::render('Checkout', [
            'items' => $items,
            'savedAddress' => $request->user()->alamat,
            'minimumDate' => Carbon::tomorrow()->toDateString(),
        ]);
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

        $order = DB::transaction(function () use ($data, $request): Order {
            $items = Item::query()->whereIn('id', collect($data['items'])->pluck('id'))->get()->keyBy('id');
            $subtotal = 0;
            foreach ($data['items'] as $line) {
                $subtotal += $items[$line['id']]->harga * $line['quantity'];
            }
            $discount = $data['kode_voucher'] === 'CEMIL10' ? round($subtotal * 0.1, 2) : 0;
            $order = Order::create([
                'id_user' => $request->user()->id,
                'total_harga' => $subtotal - $discount,
                'status_pembayaran' => 'pending',
                'status_pesanan' => 'pending',
                'alamat_pengiriman' => $data['alamat_pengiriman'],
                'tanggal_pesan' => $data['tanggal_pesan'],
                'kode_voucher' => $data['kode_voucher'] ?? null,
                'diskon' => $discount,
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
        $order->update([
            'midtrans_order_id' => $snapData['order_id'] ?? $order->id_order,
            'snap_token' => $snapData['token'] ?? null,
            'payment_url' => $snapData['redirect_url'] ?? null,
            'transaction_id' => $snapData['transaction_id'] ?? null,
            'transaction_status' => 'pending',
        ]);

        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'message' => 'Pesanan berhasil dibuat.',
                'order_id' => $order->id_order,
                'snap_token' => $snapData['token'] ?? null,
                'payment_url' => $snapData['redirect_url'] ?? null,
                'order' => $order->fresh()->load('orderDetails.item'),
            ], 201);
        }

        return to_route('orders.history')->with('success', "Pesanan #{$order->id_order} berhasil dibuat. Lanjutkan pembayaran melalui Midtrans.");
    }
}
