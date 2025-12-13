<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Socialite;

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
        
        // Check if user is authenticated (for binding flow)
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

    public function BindLineAccount(Request $request)
    {
        // Redirect to LINE for OAuth authorization
        return Socialite::driver('line')
            ->redirect();
    }
}
