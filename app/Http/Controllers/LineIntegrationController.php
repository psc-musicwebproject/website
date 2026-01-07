<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Socialite;
use App\Models\User;
// Add logging for debugging purposes
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

// Importing LINE SDK v12
use GuzzleHttp\Client;
use LINE\Clients\MessagingApi\Configuration;
use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Model\PushMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Clients\MessagingApi\Model\FlexMessage;
use LINE\Clients\MessagingApi\Model\FlexBubble;
use LINE\Clients\MessagingApi\ApiException;


class LineIntegrationController extends Controller
{
    protected $messagingApi;

    public function __construct()
    {
        $accessToken = config('services.line.messaging_api.access_token');
        
        if (empty($accessToken)) {
            Log::error('LINE Messaging API access token is not configured');
            throw new \RuntimeException('LINE Messaging API access token is missing');
        }
        
        $client = new Client();
        $config = new Configuration();
        $config->setAccessToken($accessToken);
        
        $this->messagingApi = new MessagingApiApi(
            client: $client,
            config: $config
        );
    }

    public function getLineBot()
    {
        return $this->messagingApi;
    }

    public function AuthenticateViaLine(Request $request)
    {
        // Logic for authenticating with LINE API
        $guard = $request->query('guard', 'web');
        
        session()->forget('line_binding_mode');
        session([
            'auth_via_line' => true,
            'line_auth_guard' => $guard
        ]);
        session()->save(); // Force save before redirect
        
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
        
        // Determine current user ID from either guard
        $currentUserId = 'guest';
        if (Auth::guard('admin')->check()) {
            $currentUserId = Auth::guard('admin')->id();
        } elseif (Auth::guard('web')->check()) {
            $currentUserId = Auth::guard('web')->id();
        }
        
        Log::info('Received LINE callback', [
            'line_id' => $lineUser->id ?? 'unknown',
            'binding_mode' => $isBindingMode,
            'isAuth' => session('auth_via_line', false),
            'stored_user_id' => $bindingUserId,
            'current_user_id' => $currentUserId
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

            // Get guard and redirect URL BEFORE forgetting session
            $guard = session('line_binding_guard', 'web');
            $redirectUrl = session('line_bind_redirect');
            session()->forget(['line_binding_mode', 'line_binding_user_id', 'line_binding_guard', 'line_bind_redirect']);
            Log::info('Successfully bound LINE ID ' . $lineUser->id . ' to user ID: ' . $user->id);

            // Use stored redirect URL if available
            if ($redirectUrl) {
                return redirect($redirectUrl)->with('status', 'การผูกบัญชี LINE สำเร็จแล้ว');
            }

            if ($guard === 'admin') {
                return redirect()->route('admin.dash')->with('status', 'การผูกบัญชี LINE สำเร็จแล้ว');
            }
            
            return redirect()->route('dash')->with('status', 'การผูกบัญชี LINE สำเร็จแล้ว');
        }
        
        // Future: Handle authentication flow (login with LINE)
        if (session('auth_via_line', false)) {
            $guard = session('line_auth_guard', 'web');
            session()->forget(['auth_via_line', 'line_auth_guard']);

            // Find user by LINE ID
            $user = User::where('line_id', $lineUser->id)->first();
            
            if ($user) {
                // For admin guard, verify user is an admin
                if ($guard === 'admin' && $user->type !== 'admin') {
                    return back()->withErrors([
                        'credentials' => 'คุณไม่มีสิทธิ์เข้าถึงส่วนผู้ดูแลระบบ',
                    ]);
                }
                
                // Login the user directly (LINE authentication is already validated)
                Auth::guard($guard)->login($user);
                $request->session()->regenerate();

                // Send messesage to user via LINE Messaging API that login was successful
                try {
                    $this->SendNormalTextMessage($lineUser->id, 'คุณ ' . $user->name . ' เข้าสู่ระบบสำเร็จแล้ว, ยินดีต้อนรับเข้าสู่ระบบ!');
                } catch (ApiException $e) {
                    Log::error('Failed to send LINE login success message: ' . $e->getMessage());
                }

                if ($guard === 'admin') {
                    return redirect()->intended('/admin');
                }
                
                return redirect()->intended('/dash');
            }
            
            return back()->withErrors([
                'credentials' => 'ไม่พบบัญชีที่ผูกกับ LINE นี้ในระบบ',
            ]);
        }

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
        // Detect which guard the user is authenticated with
        $guard = 'web';
        $user = null;
        
        if (Auth::guard('admin')->check()) {
            $guard = 'admin';
            $user = Auth::guard('admin')->user();
        } elseif (Auth::guard('web')->check()) {
            $guard = 'web';
            $user = Auth::guard('web')->user();
        }
        
        if (!$user) {
            return redirect()->route('login')->withErrors([
                'auth_error' => 'กรุณาเข้าสู่ระบบก่อนผูกบัญชี LINE',
            ]);
        }
        
        // Store binding intent and user ID in session
        session([
            'line_binding_mode' => true,
            'line_binding_user_id' => $user->id,
            'line_binding_guard' => $guard
        ]);
        session()->save(); // Force save before redirect

        Log::info('Initiating LINE account binding process for user ID: ' . $user->id . ' (guard: ' . $guard . ')');
        
        // Redirect to LINE for OAuth authorization
        return Socialite::driver('line')
            ->redirect();
    }

    public function SendNormalTextMessage($to, $msg)
    {
        try {
            $message = new TextMessage(['text' => $msg]);
            $request = new PushMessageRequest([
                'to' => $to,
                'messages' => [$message],
            ]);
            
            $this->messagingApi->pushMessage($request);
            return true;
        } catch (ApiException $e) {
            Log::error('LINE API Error: ' . $e->getCode() . ' ' . $e->getResponseBody());
            return false;
        }
    }

    public function pushFlexMessage($to, $altText = 'Flex Message', $flexContainer = null)
    {
        try {
            // Default flex bubble if none provided
            if ($flexContainer === null) {
                $flexContainer = [
                    'type' => 'bubble',
                    'hero' => [
                        'type' => 'image',
                        'url' => 'https://example.com/image.jpg',
                        'size' => 'full',
                        'aspectRatio' => '20:13',
                    ],
                    'body' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => 'Product Name',
                                'weight' => 'bold',
                                'size' => 'xl',
                            ],
                        ],
                    ],
                ];
            }
            
            $flexMessage = new FlexMessage([
                'type' => 'flex',
                'altText' => $altText,
                'contents' => $flexContainer,
            ]);
            
            $request = new PushMessageRequest([
                'to' => $to,
                'messages' => [$flexMessage],
            ]);
            
            $this->messagingApi->pushMessage($request);
            return true;
        } catch (ApiException $e) {
            Log::error('LINE Flex Message Error: ' . $e->getCode() . ' ' . $e->getResponseBody());
            return false;
        }
    }
}
