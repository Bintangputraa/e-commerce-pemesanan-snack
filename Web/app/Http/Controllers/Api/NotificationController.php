<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->notifications()->with('user')->latest()->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'pesan' => ['required', 'string'],
            'status_baca' => ['sometimes', 'boolean'],
        ]);
        $notification = Notification::create(['id_user' => $request->user()->id, ...$data]);

        return response()->json($notification->load('user'), 201);
    }

    public function update(Request $request, Notification $notification): JsonResponse
    {
        abort_unless($notification->id_user === $request->user()->id, 404);
        $notification->update($request->validate([
            'judul' => ['sometimes', 'required', 'string', 'max:255'],
            'pesan' => ['sometimes', 'required', 'string'],
            'status_baca' => ['sometimes', 'required', 'boolean'],
        ]));

        return response()->json($notification->load('user'));
    }

    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        abort_unless($notification->id_user === $request->user()->id, 404);
        $notification->delete();

        return response()->json(status: 204);
    }
}
