<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Socialite;
use App\Models\User;

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

        if (!$lineUser || empty($lineUser->id)) {
            return redirect()->route('auth.line.bind')->withErrors([
                'line_error' => 'ไม่สามารถรับข้อมูลจาก LINE ได้ กรุณาลองใหม่อีกครั้ง',
            ]);
        }

        // Check if user is authenticated (for binding flow)
        if ($request->user()) {
            // Check if this LINE ID is already bound to a different user
            if (User::isThisLineIDAlreadyBound($lineUser->id, $request->user()->id)) {
                return redirect()->route('auth.line.bind')->withErrors([
                    'line_error' => 'บัญชี LINE นี้ถูกผูกกับผู้ใช้อื่นแล้ว',
                ]);
            }
            
            // Bind LINE ID to current user
        if ($request->user() && !$request->user()->line_bound) {
            $user = $request->user();
            $user->line_id = $lineUser->id;
            $user->line_bound = true;
            $user->save();
            
            return redirect()->route('dash')->with('status', 'การผูกบัญชี LINE สำเร็จแล้ว');
        }
        
        // Otherwise, just return the user info (for other purposes)
        return response()->json($lineUser);
        }
    }

    public function BindLineAccount(Request $request)
    {
        // Redirect to LINE for OAuth authorization
        return Socialite::driver('line')
            ->redirect();
    }
}
