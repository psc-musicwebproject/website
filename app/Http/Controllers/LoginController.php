<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LineIntegrationController;
use LINE\Clients\MessagingApi\ApiException;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    // Inject the LineController so Laravel handles dependencies automatically
    public function __construct(
        protected LineIntegrationController $lineController
    ) {}

    public function __invoke(Request $request): RedirectResponse
    {
        $action = $request->input('action');

        // 1. Handle LINE Login Delegate
        if ($action === 'line_login') {
            return $this->lineController->AuthenticateViaLine($request);
        }

        // 2. Validate Action
        if ($action !== 'cred_login') {
            return back()->withErrors(['action' => 'Invalid action specified.']);
        }

        // 3. Handle Credentials Login
        $credentials = $request->validate([
            'student_id' => ['required', 'numeric'],
            'password'   => ['required'],
        ]);

        $guard = $request->query('guard', 'web');

        // Attempt login
        if (Auth::guard($guard)->attempt($credentials)) {
            $request->session()->regenerate();

            /** * BEST PRACTICE: Use config() instead of env() 
             * Ensure you have 'line_enabled' => env('LINE_ENABLED', false) in a config file.
             */
            $lineEnabled = config('app.line_enabled', false);

            // LINE Binding Check
            if ($lineEnabled && in_array($guard, ['web', 'admin'])) {
                $user = Auth::guard($guard)->user();

                if (empty($user->line_id)) {
                    // Assuming 'line_bound' is a column you need to toggle
                    $user->line_bound = false;
                    $user->save();

                    // Store the intended URL in session so it's preserved through the binding flow
                    $intendedUrl = session()->pull('url.intended', $guard === 'admin' ? '/admin' : '/dash');
                    session(['line_intended_url' => $intendedUrl]);

                    // Preserve the current guard when redirecting so the
                    // bind route can operate in the same auth context.
                    return redirect()->route('auth.line.bind', ['guard' => $guard]);
                }
            }

            // Success Redirect
            // Send messesage to user via LINE Messaging API that login was successful

            $user = Auth::guard($guard)->user();

            try {
                $this->lineController->SendNormalTextMessage($user->line_id, 'คุณ ' . $user->name . ' เข้าสู่ระบบสำเร็จแล้ว, ยินดีต้อนรับเข้าสู่ระบบ!');
            } catch (ApiException $e) {
                Log::error('Failed to send LINE login success message: ' . $e->getMessage());
            }

            return redirect()->intended($guard === 'admin' ? '/admin' : '/dash');
        }

        // 4. Handle Failures (Admin specifics)
        if ($guard === 'admin') {
            // Check if they are a valid user but NOT an admin
            // We use 'once' to check credentials without actually logging them into the session
            if (Auth::guard('web')->once($credentials)) {
                return back()
                    ->withErrors(['access' => 'Unauthorized access. Admin privileges required.'])
                    ->onlyInput('student_id');
            }
        }

        return back()
            ->withErrors(['credentials' => 'The provided credentials do not match our records.'])
            ->onlyInput('student_id');
    }
}
