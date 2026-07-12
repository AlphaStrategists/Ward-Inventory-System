<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function login() {
        return view('components/home');
    }

    public function showPasswordResetForm() {
        return 'password reset form';
    }

}
