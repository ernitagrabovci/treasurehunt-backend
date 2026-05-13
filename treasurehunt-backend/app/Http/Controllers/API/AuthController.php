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
                'message' => 'Gabime në validim',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $this->sendVerificationEmail($user);

        return response()->json([
            'success' => true,
            'message' => 'Regjistrimi u krye! Ju lutemi kontrolloni email-in për të verifikuar llogarinë para se të hyni.',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'verification_hash' => sha1($user->email),
            ]
        ], 201);
    }

    // Send verification email with clickable button
    private function sendVerificationEmail($user)
    {
        $verificationUrl = url("/api/email/verify/{$user->id}/" . sha1($user->email));
        $htmlContent = view('emails.verify', compact('user', 'verificationUrl'))->render();

        Mail::html($htmlContent, function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Verifiko Email-in - Gjueti Thesari Kosova');
        });
    }

    // Verify email from the link (returns JSON for API, HTML for browser)
    public function verifyEmail($id, $hash)
    {
        $user = User::findOrFail($id);

        if (sha1($user->email) !== $hash) {
            return view('verify-success', ['loginUrl' => url('/'), 'error' => 'Invalid verification link']);
        }

        if ($user->email_verified_at) {
            return view('verify-success', ['loginUrl' => url('/'), 'error' => 'Email already verified']);
        }

        $user->email_verified_at = now();
        $user->save();

        $loginUrl = url('/');
        return view('verify-success', compact('loginUrl'));
    }

    // Verify email via JSON (for mobile app)
    public function verifyEmailJson(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'hash' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification data',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($request->user_id);

        if (sha1($user->email) !== $request->hash) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link'
            ], 400);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified'
            ], 400);
        }

        $user->email_verified_at = now();
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully'
        ]);
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
                'message' => 'Email-i tashmë është i verifikuar'
            ], 400);
        }

        $this->sendVerificationEmail($user);

        return response()->json([
            'success' => true,
            'message' => 'Email-i i verifikimit u dërgua! Kontrolloni inbox-in tuaj.'
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
                'message' => 'Kredenciale të pavlefshme'
            ], 401);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Ju lutemi verifikoni email-in fillimisht. Kontrolloni inbox-in për lidhjen e verifikimit.',
                'requires_verification' => true,
                'email' => $user->email
            ], 401);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Kredenciale të pavlefshme'
            ], 401);
        }

        $token = $user->createToken('treasure_hunt_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Mirësevini përsëri në Gjuetinë e Thesarit Kosova!',
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
        if ($request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Dolët nga llogaria me sukses'
        ]);
    }
}
