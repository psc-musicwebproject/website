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
        $lineUser = Socialite::driver('line')->user();
        
        // Check if this is a binding flow
        $isBindingMode = session('line_binding_mode', false);
        $bindingUserId = session('line_binding_user_id');
        
        Log::info('Received LINE callback', [
            'line_id' => $lineUser->id ?? 'unknown',
            'binding_mode' => $isBindingMode,
            'stored_user_id' => $bindingUserId,
            'current_user_id' => $request->user() ? $request->user()->id : 'guest'
        ]);

        if (!$lineUser || empty($lineUser->id)) {
            return redirect()->route('auth.line.bind')->withErrors([
                'line_error' => 'ไม่สามารถรับข้อมูลจาก LINE ได้ กรุณาลองใหม่อีกครั้ง',
            ]);
        } 
        
        // Handle binding mode - use stored user ID if session auth is lost
        if ($isBindingMode && $bindingUserId) {
            $user = User::find($bindingUserId);
            
            if (!$user) {
                session()->forget(['line_binding_mode', 'line_binding_user_id']);
                return redirect()->route('auth.line.bind')->withErrors([
                    'line_error' => 'ไม่พบข้อมูลผู้ใช้ กรุณาลองใหม่อีกครั้ง',
                ]);
            }
            
            // Check if this LINE ID is already bound to another user
            if (User::isThisLineIDAlreadyBound($lineUser->id, $user->id)) {
                session()->forget(['line_binding_mode', 'line_binding_user_id']);
                return redirect()->route('auth.line.bind')->withErrors([
                    'line_error' => 'บัญชี LINE นี้ถูกผูกกับผู้ใช้อื่นแล้ว',
                ]);
            }
            
            $user->line_id = $lineUser->id;
            $user->line_bound = true;
            $user->save();

            session()->forget(['line_binding_mode', 'line_binding_user_id']);
            Log::info('Successfully bound LINE ID ' . $lineUser->id . ' to user ID: ' . $user->id);
            
            $guard = $request->query('guard', 'web');
            if ($guard === 'admin') {
                return redirect()->route('admin.dashboard')->with('status', 'การผูกบัญชี LINE สำเร็จแล้ว');
            }
            
            return redirect()->route('dash')->with('status', 'การผูกบัญชี LINE สำเร็จแล้ว');
        }
        
        // Future: Handle authentication flow (login with LINE)
        // Check if LINE ID exists in database
        $existingUser = User::where('line_id', $lineUser->id)->first();
        if ($existingUser) {
            // Prompt user to login to their existing account
            return redirect()->route('login')->with('info', 'พบบัญชีที่ผูกกับ LINE นี้แล้ว');
        }
        
        // Otherwise, just return the user info (for other purposes)
        return response()->json($lineUser);
    }

    public function BindLineAccount(Request $request)
    {
        // Store binding intent and user ID in session
        session([
            'line_binding_mode' => true,
            'line_binding_user_id' => $request->user()->id
        ]);
        session()->save(); // Force save before redirect

        Log::info('Initiating LINE account binding process for user ID: ' . $request->user()->id);
        
        // Redirect to LINE for OAuth authorization
        return Socialite::driver('line')
            ->redirect();
    }
}
