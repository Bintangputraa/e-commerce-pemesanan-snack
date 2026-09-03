<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $items = Item::query()
            ->latest()
            ->get()
            ->map(fn (Item $item): array => [
                'id' => $item->id,
                'name' => $item->nama,
                'description' => $item->deskripsi,
                'category' => $item->kategori,
                'price' => (float) $item->harga,
                'image' => $item->gambar,
            ])
            ->values();

        return Inertia::render('Welcome', [
            'items' => $items,
            'favoriteIds' => $request->user()
                ? Favorite::query()->where('id_user', $request->user()->id)->pluck('id_item')->values()
                : [],
        ]);
    }
}
