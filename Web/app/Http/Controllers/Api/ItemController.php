<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Item::query()->latest()->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $item = Item::create($request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'harga' => ['required', 'numeric', 'min:0'],
            'gambar' => ['nullable', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:255'],
        ]));

        return response()->json($item, 201);
    }

    public function update(Request $request, Item $item): JsonResponse
    {
        $item->update($request->validate([
            'nama' => ['sometimes', 'required', 'string', 'max:255'],
            'deskripsi' => ['sometimes', 'nullable', 'string'],
            'harga' => ['sometimes', 'required', 'numeric', 'min:0'],
            'gambar' => ['sometimes', 'nullable', 'string', 'max:255'],
            'kategori' => ['sometimes', 'required', 'string', 'max:255'],
        ]));

        return response()->json($item);
    }

    public function destroy(Item $item): JsonResponse
    {
        $item->delete();

        return response()->json(status: 204);
    }
}
