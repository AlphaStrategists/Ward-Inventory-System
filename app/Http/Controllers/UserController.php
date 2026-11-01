<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login() {
        return view('pages.login');
    }

    public function home() {
        return view('pages.home');
    }

    public function showPasswordResetForm() {
        return 'password reset form';
    }

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
            'role' => 'required|in:Doctor,Nurse,Pharmacist,Head Nurse',
            'email' => 'required|string|email|max:255|unique:users,email'
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_admin' => 0,
        ]);

        return redirect()->route('ward.login')
            ->with('success', 'User created successfully.');
        
    }

    public function show($email)
    {
        $user = User::findOrFail($email);

        return response()->json($user);
    }
}
