<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Notification::query()->with('user')->latest()->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $notification = Notification::create($request->validate([
            'id_user' => ['required', 'integer', 'exists:users,id'],
            'judul' => ['required', 'string', 'max:255'],
            'pesan' => ['required', 'string'],
            'status_baca' => ['sometimes', 'boolean'],
        ]));

        return response()->json($notification->load('user'), 201);
    }

    public function update(Request $request, Notification $notification): JsonResponse
    {
        $notification->update($request->validate([
            'id_user' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'judul' => ['sometimes', 'required', 'string', 'max:255'],
            'pesan' => ['sometimes', 'required', 'string'],
            'status_baca' => ['sometimes', 'required', 'boolean'],
        ]));

        return response()->json($notification->load('user'));
    }

    public function destroy(Notification $notification): JsonResponse
    {
        $notification->delete();

        return response()->json(status: 204);
    }
}
