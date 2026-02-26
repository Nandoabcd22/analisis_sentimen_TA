<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if (Auth::attempt($validated, $request->boolean('remember'))) {
            $request->session()->regenerate();
            Log::info('User logged in', ['user_id' => Auth::id(), 'email' => Auth::user()->email]);
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.'
        ])->onlyInput('email');
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed'
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password'])
            ]);

            Auth::login($user);
            Log::info('New user registered', ['user_id' => $user->id, 'email' => $user->email]);
            
            return redirect()->route('dashboard')->with('success', 'Registrasi berhasil! Selamat datang.');
        } catch (\Exception $e) {
            Log::error('Registration error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat registrasi.')->withInput();
        }
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Log::info('User logged out', ['user_id' => Auth::id()]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }
}
