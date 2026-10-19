<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return response()->json($users);
    }

    public function create()
    {
        return view('register');
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'password' => 'required|string|confirmed',
            'role' => 'required|in:nurse',
            'email' => 'required|string|email|max:255|unique:users,email'
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_admin' => false,
        ]);

        dump($user->getAttributes());
        return redirect()->route('login')
            ->with('success', 'User created successfully.');
        
    }

    public function show($email)
    {
        $user = User::findOrFail($email);

        return response()->json($user);
    }
}
