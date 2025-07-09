<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Redirect to OAuth provider
     */
    public function redirect($provider)
    {
        // Validate provider
        if (!in_array($provider, ['google', 'github'])) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not supported'
            ], 400);
        }

        try {
            $redirectUrl = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
            
            return response()->json([
                'success' => true,
                'redirect_url' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error redirecting to ' . $provider,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle OAuth callback
     */
    public function callback($provider)
    {
        // Validate provider
        if (!in_array($provider, ['google', 'github'])) {
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/auth/error?error=provider_not_supported');
        }

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            
            // Try to find existing user by provider
            $user = User::where('provider', $provider)
                       ->where('provider_id', $socialUser->getId())
                       ->first();

            // If not found, try to find by email
            if (!$user) {
                $user = User::where('email', $socialUser->getEmail())->first();
                
                if ($user) {
                    // Update existing user with OAuth info
                    $user->update([
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'avatar' => $socialUser->getAvatar(),
                    ]);
                }
            }

            // Create new user if doesn't exist
            if (!$user) {
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'email_verified_at' => now(),
                    'password' => null, // OAuth users don't need password
                ]);
            }

            // Create access token
            $token = $user->createToken('api-token')->plainTextToken;

            // Redirect to frontend with success data
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            $userData = base64_encode(json_encode([
                'user' => $user,
                'token' => $token,
                'provider' => $provider
            ]));
            
            return redirect($frontendUrl . '/auth/callback?success=1&data=' . $userData);

        } catch (\Exception $e) {
            // Redirect to frontend with error
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            $errorMessage = 'Error authenticating with ' . $provider . ': ' . $e->getMessage();
            
            return redirect($frontendUrl . '/auth/callback?error=' . urlencode($errorMessage));
        }
    }

    /**
     * Handle frontend OAuth - returns redirect URL
     */
    public function getRedirectUrl($provider)
    {
        // Validate provider
        if (!in_array($provider, ['google', 'github'])) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not supported'
            ], 400);
        }

        try {
            // Generate a temporary state for security
            $state = Str::random(40);
            
            $redirectUrl = Socialite::driver($provider)
                ->stateless()
                ->redirect()
                ->getTargetUrl();
            
            return response()->json([
                'success' => true,
                'redirect_url' => $redirectUrl,
                'provider' => $provider
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting redirect URL for ' . $provider,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle OAuth token exchange (for frontend SPA)
     */
    public function handleToken(Request $request, $provider)
    {
        // Validate provider
        if (!in_array($provider, ['google', 'github'])) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not supported'
            ], 400);
        }

        try {
            $code = $request->input('code');
            if (!$code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization code is required'
                ], 400);
            }

            // Get user from provider using the code
            $socialUser = Socialite::driver($provider)->stateless()->user();
            
            // Try to find existing user by provider
            $user = User::where('provider', $provider)
                       ->where('provider_id', $socialUser->getId())
                       ->first();

            // If not found, try to find by email
            if (!$user) {
                $user = User::where('email', $socialUser->getEmail())->first();
                
                if ($user) {
                    // Update existing user with OAuth info
                    $user->update([
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'avatar' => $socialUser->getAvatar(),
                    ]);
                }
            }

            // Create new user if doesn't exist
            if (!$user) {
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'email_verified_at' => now(),
                    'password' => null, // OAuth users don't need password
                ]);
            }

            // Create access token
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Successfully authenticated with ' . $provider,
                'user' => $user,
                'token' => $token
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing OAuth token for ' . $provider,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
