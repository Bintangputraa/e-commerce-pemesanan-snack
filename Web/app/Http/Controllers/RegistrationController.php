<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    public function __invoke(Request $request, CreateNewUser $createNewUser): RedirectResponse
    {
        $user = $createNewUser->create($request->all());

        Auth::guard('web')->login($user);

        return redirect('/');
    }
}
