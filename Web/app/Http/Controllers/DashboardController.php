<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return $request->user()->role === UserRole::Admin
            ? redirect()->route('admin.dashboard')
            : redirect()->route('orders.history');
    }
}
