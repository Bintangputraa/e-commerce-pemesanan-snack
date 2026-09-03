<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Item;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class CustomerOrderController extends Controller
{
    public function __construct(protected MidtransService $midtransService) {}

    public function history(Request $request): Response
    {
        $orders = $request->user()->orders()->whereIn('status_pembayaran', ['pending', 'paid'])->get();
        foreach ($orders as $order) {
            try {
                $this->midtransService->synchronizeStatus($order);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return Inertia::render('orders/History', [
            'orders' => $request->user()->orders()->latest()->with(['user', 'orderDetails.item'])->get(),
            'title' => 'History Ordered',
        ]);
    }

    public function process(Request $request): Response
    {
        $orders = $request->user()->orders()->whereIn('status_pembayaran', ['pending', 'paid'])->get();
        foreach ($orders as $order) {
            try {
                $this->midtransService->synchronizeStatus($order);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return Inertia::render('orders/History', [
            'orders' => $request->user()->orders()
                ->where('status_pembayaran', 'paid')
                ->whereDate('tanggal_pesan', '>=', Carbon::today())
                ->latest()
                ->with(['user', 'orderDetails.item'])
                ->get(),
            'title' => 'On Process',
        ]);
    }

    public function pay(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->id_user === $request->user()->id, 403);

        if (! in_array($order->status_pembayaran, ['pending', 'failed'], true)) {
            return response()->json([
                'message' => 'Pesanan ini tidak dapat dibayar lagi.',
            ], 422);
        }

        $snapData = $this->midtransService->createTransaction($order);

        $order->update([
            'midtrans_order_id' => $snapData['order_id'] ?? $order->midtrans_order_id,
            'snap_token' => $snapData['token'] ?? $order->snap_token,
            'payment_url' => $snapData['redirect_url'] ?? $order->payment_url,
            'transaction_id' => $snapData['transaction_id'] ?? $order->transaction_id,
            'transaction_status' => 'pending',
        ]);

        return response()->json([
            'order_id' => $order->id_order,
            'snap_token' => $snapData['token'] ?? $order->snap_token,
            'payment_url' => $snapData['redirect_url'] ?? $order->payment_url,
        ]);
    }

    public function paymentStatus(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->id_user === $request->user()->id, 403);

        if (blank($order->midtrans_order_id)) {
            return response()->json(['order' => $order]);
        }

        try {
            $order = $this->midtransService->synchronizeStatus($order);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Status pembayaran belum dapat disinkronkan.',
                'order' => $order,
            ], 502);
        }

        return response()->json(['order' => $order]);
    }

    public function favorites(Request $request): Response
    {
        return Inertia::render('orders/Favorites', [
            'favorites' => Favorite::query()
                ->where('id_user', $request->user()->id)
                ->with('item')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function toggleFavorite(Request $request, int $itemId): RedirectResponse
    {
        Item::query()->findOrFail($itemId);

        $favorite = Favorite::query()
            ->where('id_user', $request->user()->id)
            ->where('id_item', $itemId)
            ->first();

        if ($favorite) {
            $favorite->delete();
        } else {
            Favorite::create([
                'id_user' => $request->user()->id,
                'id_item' => $itemId,
            ]);
        }

        return back();
    }
}
