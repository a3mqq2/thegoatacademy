<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function do_login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['nullable'],
        ]);

        $remember = $request->filled('remember');
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !$user->status) {
            return back()->withErrors([
                'email' => 'Sorry, your account is not active.',
            ]);
        }

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            $request->session()->regenerate();
            $token = $request->user()->createToken('authToken')->plainTextToken;
            $cookie = cookie('access_token', $token, 60 * 24 * 30);
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'description' => 'User logged in',
                'type' => 'login',
                'entity_id' => Auth::id(),
                'entity_type' => User::class,
            ]);
            
            return redirect('/sections')->withCookie($cookie);
        }

        return back()->withErrors([
            'email' => 'Your provided credentials are incorrect.',
        ]);
    }

    public function logout(Request $request)
    {
        // Create audit log
        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'User logged out',
            'type' => 'logout',
            'entity_id' => Auth::id(),
            'entity_type' => User::class,
        ]);
        
        $request->user()->tokens()->delete();
        Auth::logout();
        return redirect('/login');
    }
}
