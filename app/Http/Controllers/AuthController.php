<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show the administrator login page.
     */
    public function showLogin()
    {
        // If already logged in, go directly to the dashboard.
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * Authenticate the predefined local administrator.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Predefined development administrator account.
        $adminEmail = 'veterinaryadminoffice@gmail.com';
        $adminPassword = 'Vetoffice@2026';

        // Validate the submitted credentials.
        if (
            strtolower($credentials['email']) !== strtolower($adminEmail) ||
            $credentials['password'] !== $adminPassword
        ) {
            return back()
                ->withErrors([
                    'email' => 'Invalid administrator email or password.',
                ])
                ->onlyInput('email');
        }

        // Find or create the local Laravel administrator.
        $user = User::firstOrNew([
            'email' => $adminEmail,
        ]);

        if (!$user->exists) {
            $user->name = 'System Administrator';
            $user->password = Hash::make($adminPassword);
            $user->save();
        }

        // Start the Laravel session.
        Auth::login($user);
        $request->session()->regenerate();

        // Redirect to the dashboard.
        return redirect()->route('admin.dashboard');
    }

    /**
     * Log out the administrator.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
