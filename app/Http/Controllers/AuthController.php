<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Auto-seed users if database is empty (fallback for when seeder doesn't run)
        if (User::count() === 0) {
            $defaultUsers = [
                ['name' => 'Akbar', 'email' => 'akbar@spindo.com', 'password' => 'password'],
                ['name' => 'Reo',   'email' => 'reo@spindo.com',   'password' => 'password'],
                ['name' => 'Dendi', 'email' => 'dendi@spindo.com', 'password' => 'password'],
            ];
            foreach ($defaultUsers as $u) {
                User::create($u);
            }
        }

        $users = User::all();
        return view('auth.login', compact('users'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang, ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Berhasil keluar.');
    }
}
