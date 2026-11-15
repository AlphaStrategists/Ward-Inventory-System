<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {

            $request->session()->regenerate();

            // Use role_id because your users table
            // currently has NULL values in the role column.
            $roleId = (int) Auth::user()->role_id;

           $destination = match ($roleId) {
                1 => route('home'),
                2 => route('home'),
                3 => route('home'),
                4 => route('home'),
                default => route('login'),
            };

return redirect()->intended($destination);

            return redirect()->intended($destination);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
