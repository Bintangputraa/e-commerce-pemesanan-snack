<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'whatsapp' => ['required', 'string', 'max:25'],
            'alamat' => ['required', 'string', 'max:1000'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
        $data['role'] = UserRole::Customer;
        $user = User::create($data);

        return $this->tokenResponse($user, 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 422);
        }

        return $this->tokenResponse($user);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    public function logout(Request $request): JsonResponse
    {
        $plainToken = $request->bearerToken();
        if ($plainToken) {
            ApiToken::query()->where('token', hash('sha256', $plainToken))->delete();
        }

        return response()->json(['message' => 'Logout berhasil.']);
    }

    private function tokenResponse(User $user, int $status = 200): JsonResponse
    {
        $plainToken = Str::random(40);
        ApiToken::query()->where('user_id', $user->id)->delete();
        ApiToken::query()->create([
            'user_id' => $user->id,
            'token' => hash('sha256', $plainToken),
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'token' => $plainToken,
            'token_type' => 'Bearer',
            'expires_at' => now()->addDays(30)->toISOString(),
            'user' => $user,
        ], $status);
    }
}
