<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FavoriteController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Favorite::query()->with(['user', 'item'])->latest()->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $favorite = Favorite::create($request->validate([
            'id_user' => ['required', 'integer', 'exists:users,id'],
            'id_item' => [
                'required',
                'integer',
                'exists:items,id',
                Rule::unique('favorites')->where(fn ($query) => $query->where('id_user', $request->integer('id_user'))),
            ],
        ]));

        return response()->json($favorite->load(['user', 'item']), 201);
    }

    public function update(Request $request, Favorite $favorite): JsonResponse
    {
        $data = $request->validate([
            'id_user' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'id_item' => ['sometimes', 'required', 'integer', 'exists:items,id'],
        ]);
        $favorite->update($data);

        return response()->json($favorite->load(['user', 'item']));
    }

    public function destroy(Favorite $favorite): JsonResponse
    {
        $favorite->delete();

        return response()->json(status: 204);
    }
}
