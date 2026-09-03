<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->favorites()->with(['user', 'item'])->latest()->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_item' => [
                'required',
                'integer',
                'exists:items,id',
                Rule::unique('favorites')->where(fn ($query) => $query->where('id_user', $request->user()->id)),
            ],
        ]);
        $favorite = Favorite::create(['id_user' => $request->user()->id, ...$data]);

        return response()->json($favorite->load(['user', 'item']), 201);
    }

    public function update(Request $request, Favorite $favorite): JsonResponse
    {
        abort_unless($favorite->id_user === $request->user()->id, 404);
        $data = $request->validate([
            'id_item' => ['sometimes', 'required', 'integer', 'exists:items,id'],
        ]);
        $favorite->update($data);

        return response()->json($favorite->load(['user', 'item']));
    }

    public function destroy(Request $request, Favorite $favorite): JsonResponse
    {
        abort_unless($favorite->id_user === $request->user()->id, 404);
        $favorite->delete();

        return response()->json(status: 204);
    }
}
