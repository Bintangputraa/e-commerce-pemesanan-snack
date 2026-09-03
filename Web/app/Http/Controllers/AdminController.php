<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function dashboard(): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'orders' => Order::count(),
                'pendingOrders' => Order::whereIn('status_pesanan', ['pending', 'processing'])->count(),
                'items' => Item::count(),
                'revenue' => (float) Order::where('status_pembayaran', 'paid')->sum('total_harga'),
            ],
            'recentOrders' => Order::with('user')->latest()->limit(8)->get(),
        ]);
    }

    public function orders(): Response
    {
        return Inertia::render('admin/Orders', [
            'orders' => Order::with('user')->latest()->paginate(15),
        ]);
    }

    public function items(): Response
    {
        return Inertia::render('admin/Items', [
            'items' => Item::latest()->paginate(15),
        ]);
    }

    public function storeItem(Request $request): RedirectResponse
    {
        Item::create($this->itemData($request));

        return to_route('admin.items')->with('success', 'Menu snack berhasil ditambahkan.');
    }

    public function updateItem(Request $request, Item $item): RedirectResponse
    {
        $item->update($this->itemData($request));

        return to_route('admin.items')->with('success', 'Menu snack berhasil diperbarui.');
    }

    public function destroyItem(Item $item): RedirectResponse
    {
        $item->delete();

        return to_route('admin.items')->with('success', 'Menu snack berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function itemData(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'harga' => ['required', 'numeric', 'min:0'],
            'gambar' => ['nullable', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:255'],
        ]);
    }

    public function users(): Response
    {
        return Inertia::render('admin/Users', [
            'users' => User::query()->latest()->paginate(15),
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        User::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::enum(UserRole::class)],
        ]));

        return to_route('admin.users')->with('success', 'User berhasil ditambahkan.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $user->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'role' => ['required', Rule::enum(UserRole::class)],
        ]));

        return to_route('admin.users')->with('success', 'User berhasil diperbarui.');
    }

    public function destroyUser(User $user): RedirectResponse
    {
        $user->delete();

        return to_route('admin.users')->with('success', 'User berhasil dihapus.');
    }
}
