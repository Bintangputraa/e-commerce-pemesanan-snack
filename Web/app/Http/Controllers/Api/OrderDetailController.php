<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderDetailController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            OrderDetail::query()
                ->whereHas('order', fn ($query) => $query->where('id_user', $request->user()->id))
                ->with(['order', 'item'])
                ->paginate()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_order' => ['required', 'integer', 'exists:orders,id_order'],
            'id_item' => ['required', 'integer', 'exists:items,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'harga_satuan' => ['required', 'numeric', 'min:0'],
        ]);
        abort_unless(
            Order::query()
                ->where('id_order', $data['id_order'])
                ->where('id_user', $request->user()->id)
                ->exists(),
            404
        );
        $detail = OrderDetail::create($data);

        return response()->json($detail->load(['order', 'item']), 201);
    }

    public function update(Request $request, int $id_order, int $id_item): JsonResponse
    {
        $detail = $this->findDetail($id_order, $id_item);
        $data = $request->validate([
            'jumlah' => ['sometimes', 'required', 'integer', 'min:1'],
            'harga_satuan' => ['sometimes', 'required', 'numeric', 'min:0'],
        ]);
        OrderDetail::query()
            ->where('id_order', $id_order)
            ->where('id_item', $id_item)
            ->update($data);
        $detail->refresh();

        return response()->json($detail->load(['order', 'item']));
    }

    public function destroy(int $id_order, int $id_item): JsonResponse
    {
        $this->findDetail($id_order, $id_item);
        OrderDetail::query()
            ->where('id_order', $id_order)
            ->where('id_item', $id_item)
            ->delete();

        return response()->json(status: 204);
    }

    private function findDetail(int $id_order, int $id_item): OrderDetail
    {
        return OrderDetail::query()
            ->where('id_order', $id_order)
            ->where('id_item', $id_item)
            ->whereHas('order', fn ($query) => $query->where('id_user', request()->user()->id))
            ->firstOrFail();
    }
}
