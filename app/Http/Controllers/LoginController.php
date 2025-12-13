<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Handle an authentication attempt.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'student_id' => ['required', 'numeric'],
            'password' => ['required'],
        ]);

        // Check which guard is being requested
        $guard = $request->query('guard', 'web');
        
        // Attempt login with the appropriate guard
        if (Auth::guard($guard)->attempt($credentials)) {
            $request->session()->regenerate();

            if (env('LINE_ENABLED') && $guard === 'web' || $_COOKIE['skip_line_check'] !== 'true') {
                $user = Auth::guard('web')->user();
                if (is_null($user->line_id)) {
                    $user->line_bound = false;
                    $user->save();
                    return redirect()->route('auth.line.bind');
                }

                // Redirect based on which guard was used
                if ($guard === 'admin') {
                    return redirect()->intended('/admin');
                }
                
                return redirect()->intended('/dash');
            }
        }

        // Provide specific error messages based on the guard
        if ($guard === 'admin') {
            // Check if user exists but is not an admin
            if (Auth::guard('web')->attempt($credentials)) {
                Auth::guard('web')->logout(); // Logout from web guard
                
                return back()->withErrors([
                    'access' => 'Unauthorized access. Admin privileges required.',
                ])->onlyInput('student_id');
            }
        }

        return back()->withErrors([
            'credentials' => 'The provided credentials do not match our records.',
        ])->onlyInput('student_id');
    }
}