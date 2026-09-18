<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


abstract class Controller
{
    public function index()
    {
        $users = User::all();

        return response()->json($users);
    }

    public function create() {
        return view('test');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email'
        ]);

        try {
            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'],
            ]);

            return redirect()->route('')
                ->with('success', 'User created successfully.');
        } catch (\Exception) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating the user.');
        }
    }

    public function show($email)
    {
        $user = User::findOrFail($email);

        return response()->json($user);
    }
}
