<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create()
    {
        return view('auth.newpass');
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(4)],
        ]);

        $user = $request->user();

        $user->forceFill([
            'password' => Hash::make($request->password),
            'reset_password_on_next_login' => false,
        ])->save();

        // Redirect based on user type/role if needed, or just intended
        // Admin likely wants to go to /admin, others to /dash
        // But since intended() usually captures the original request URI (e.g. /dash or /admin)
        // that was intercepted by middleware, intended() is the best choice.
        // Fallback to /dash if intended is empty.

        $redirect = '/dash';
        if ($request->user('admin')) {
            $redirect = '/admin';
        }

        return redirect()->intended($redirect);
    }
}
