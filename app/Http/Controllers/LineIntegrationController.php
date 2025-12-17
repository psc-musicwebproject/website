<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Socialite;
use App\Models\User;
// Add logging for debugging purposes
use Illuminate\Support\Facades\Log;

class LineIntegrationController extends Controller
{
    public function AuthenticateViaLine()
    {
        // Logic for authenticating with LINE API
        return Socialite::driver('line')
            ->redirect();
    }

    public function GetCallbackFromLine(Request $request)
    {
        // Logic for handling callback from LINE API
        Log::info('Received LINE callback for user ID: ' . ($request->user() ? $request->user()->id : 'guest'));
        $lineUser = Socialite::driver('line')->user();
        
        // Check if this is a binding flow
        $isBindingMode = session('line_binding_mode', false);
        Log::info('LINE binding mode: ' . ($isBindingMode ? 'true' : 'false'));

        if (!$lineUser || empty($lineUser->id)) {
            return redirect()->route('auth.line.bind')->withErrors([
                'line_error' => 'ไม่สามารถรับข้อมูลจาก LINE ได้ กรุณาลองใหม่อีกครั้ง',
            ]);
        } 
        
        // Handle binding mode
        if ($isBindingMode && $request->user()) {
            $user = $request->user();
            
            // Check if this LINE ID is already bound to another user
            if (User::isThisLineIDAlreadyBound($lineUser->id, $user->id)) {
                return redirect()->route('auth.line.bind')->withErrors([
                    'line_error' => 'บัญชี LINE นี้ถูกผูกกับผู้ใช้อื่นแล้ว',
                ]);
            }
            
            $user->line_id = $lineUser->id;
            $user->line_bound = true;
            $user->save();

            session()->forget('line_binding_mode'); // Clear the session flag
            Log::info('Successfully bound LINE ID ' . $lineUser->id . ' to user ID: ' . $user->id);
            $guard = $request->query('guard', 'web');
            if ($guard === 'web') {
                return redirect()->route('dash')->with('status', 'การผูกบัญชี LINE สำเร็จแล้ว');
            } else if ($guard === 'admin') {
                return redirect()->route('admin.dashboard')->with('status', 'การผูกบัญชี LINE สำเร็จแล้ว');
            }
            
            return redirect()->route('dash')->with('status', 'การผูกบัญชี LINE สำเร็จแล้ว');
        }
        
        // Otherwise, just return the user info (for other purposes)
        return response()->json($lineUser);
    }

    public function BindLineAccount(Request $request)
    {
        // Store binding intent in session
        session(['line_binding_mode' => true]);

        Log::info('Initiating LINE account binding process for user ID: ' . ($request->user() ? $request->user()->id : 'guest'));
        
        // Redirect to LINE for OAuth authorization
        return Socialite::driver('line')
            ->redirect();
    }
}
