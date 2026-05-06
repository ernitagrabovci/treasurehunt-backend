<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    // Register new user
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Send verification email
        $this->sendVerificationEmail($user);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful! Please check your email to verify your account.',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email
            ]
        ], 201);
    }

    // Send verification email with clickable button
    private function sendVerificationEmail($user)
    {
        $verificationUrl = url("/api/email/verify/{$user->id}/" . sha1($user->email));
        
        $htmlContent = '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Verify Your Email</title>
            <meta charset="UTF-8">
        </head>
        <body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">
            <div style="max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <div style="text-align: center; margin-bottom: 30px;">
                    <h1 style="color: #021044; margin: 0;">🗺️ Treasure Hunt Kosovo</h1>
                </div>
                
                <h2 style="color: #021044;">Hello ' . $user->name . '!</h2>
                
                <p style="color: #333; line-height: 1.6; margin-bottom: 25px;">
                    Thank you for registering! Please click the button below to verify your email address and activate your account.
                </p>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $verificationUrl . '" style="background-color: #D8B129; color: #021044; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; font-size: 16px;">
                        Verify Email Address
                    </a>
                </div>
                
                <p style="color: #666; font-size: 12px; margin-top: 20px;">
                    Or copy and paste this link into your browser:<br>
                    <span style="color: #999; word-break: break-all;">' . $verificationUrl . '</span>
                </p>
                
                <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
                
                <p style="color: #999; font-size: 11px; text-align: center; margin: 0;">
                    If you didn\'t create an account, you can ignore this email.
                </p>
            </div>
        </body>
        </html>
        ';
        
        Mail::send([], [], function ($message) use ($user, $htmlContent) {
            $message->to($user->email)
                    ->subject('Verify Your Email - Treasure Hunt Kosovo')
                    ->setBody($htmlContent, 'text/html');
        });
    }

    // Verify email from the link (returns JSON for API, HTML for browser)
    public function verifyEmail($id, $hash)
    {
        $user = User::findOrFail($id);

        if (sha1($user->email) !== $hash) {
            // Return HTML for browser (when user clicks email link)
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification link'
                ], 400);
            }
            return '<h1>Invalid verification link</h1>';
        }

        if ($user->email_verified_at) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already verified'
                ], 400);
            }
            return '<h1>Email already verified</h1>';
        }

        $user->email_verified_at = now();
        $user->save();

        // Return HTML for browser (user clicked email link)
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Email Verified!</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                    background: linear-gradient(135deg, #021044 0%, #021044 100%);
                }
                .container {
                    text-align: center;
                    background: white;
                    padding: 40px;
                    border-radius: 20px;
                    max-width: 400px;
                }
                .success-icon {
                    width: 80px;
                    height: 80px;
                    background: #4CAF50;
                    color: white;
                    font-size: 50px;
                    line-height: 80px;
                    border-radius: 50%;
                    margin: 0 auto 20px;
                }
                h1 {
                    color: #021044;
                    margin-bottom: 10px;
                }
                p {
                    color: #666;
                    margin-bottom: 30px;
                }
                button {
                    background-color: #D8B129;
                    color: #021044;
                    border: none;
                    padding: 12px 30px;
                    font-size: 16px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-weight: bold;
                }
                button:hover {
                    background-color: #c4a020;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="success-icon">✓</div>
                <h1>Email Verified!</h1>
                <p>Your email has been successfully verified. You can now login to your account.</p>
                <button onclick="window.location.href=\'treasurehunt://login\'">Go to App</button>
                <p style="margin-top: 20px; font-size: 12px;">Or close this window and open the app</p>
            </div>
        </body>
        </html>
        ';
    }

    // Resend verification email
    public function resendVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified'
            ], 400);
        }

        $this->sendVerificationEmail($user);

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent! Please check your inbox.'
        ]);
    }

    // Login user
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email first. Check your inbox for verification link.',
                'requires_verification' => true,
                'email' => $user->email
            ], 401);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('treasure_hunt_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Welcome back to Treasure Hunt Kosovo!',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    // Get authenticated user
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}