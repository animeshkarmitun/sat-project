<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Services\UserService;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Database\Eloquent\ModelNotFoundException;



/**
 * Class AuthController
 *
 * Handles user authentication, registration, password management, and token-based authorization.
 *
 * API Routes:
 * - POST /register -> register() - Registers a new user.
 * - POST /login -> login() - Authenticates a user and issues a token.
 * - POST /logout -> logout() - Logs out the user and revokes all tokens.
 * - POST /logout/device -> logoutFromDevice() - Revokes the token of the current device.
 * - POST /password/reset -> resetPassword() - Resets user password.
 * - POST /token/refresh -> refreshToken() - Refreshes an authentication token.
 * - POST /email/resend -> resendVerificationEmail() - Resends email verification link.
 * - GET /email/verify -> checkEmailVerification() - Checks if user email is verified.
 */

class AuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|in:student,supervisor,admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = $this->userService->registerUser($request->all()); // Fixed method name
            Log::info('User registered', ['user_id' => $user->id]); // Fixed user_id reference
            return response()->json(['message' => 'Registration successful', 'user' => $user], 201);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Invalid login credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('AuthToken')->plainTextToken;
        Log::info('User logged in', ['user_id' => $user->id, 'ip' => request()->ip()]);

        return response()->json(['user' => $user, 'token' => $token], 200);
    }

    /**
     * Handle user logout (clear all tokens).
     */
    public function logout()
    {
        if (Auth::check()) {
            Auth::user()->tokens()->delete();
            Log::info('User logged out', ['user_id' => Auth::id(), 'ip' => request()->ip()]);
            return response()->json(['message' => 'Successfully logged out'], 200);
        }
        return response()->json(['error' => 'No user logged in'], 400);
    }

    /**
     * Logout from current device (single token revocation).
     */
    public function logoutFromDevice(Request $request)
    {
        $token = $request->bearerToken();
        if ($token) {
            $personalAccessToken = PersonalAccessToken::findToken($token);
            if (!$personalAccessToken) {
                return response()->json(['error' => 'Invalid token or already logged out'], 400);
            }
            $personalAccessToken->delete();
            Log::info('User logged out from a single device', ['user_id' => Auth::id(), 'ip' => request()->ip()]);
            return response()->json(['message' => 'Logged out from this device'], 200);
        }
        return response()->json(['error' => 'Invalid token'], 400);
    }

    /**
     * Handle password reset request.
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = User::where('email', $request->email)->firstOrFail();
            $user->password = Hash::make($request->password);
            $user->save();

            Log::info('User password reset', ['user_id' => $user->id, 'ip' => request()->ip()]);
            return response()->json(['message' => 'Password reset successful'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    /**
     * Refresh authentication token.
     */
    public function refreshToken(Request $request)
    {
        $user = Auth::user();
        $token = $user->createToken('AuthToken')->plainTextToken;

        Log::info('User token refreshed', ['user_id' => $user->id, 'ip' => request()->ip()]);
        return response()->json(['token' => $token], 200);
    }

    /**
     * Resend email verification.
     */
    public function resendVerificationEmail(Request $request)
    {
        $user = Auth::user();
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 400);
        }

        $user->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification email sent'], 200);
    }

    /**
     * Check email verification status.
     */
    public function checkEmailVerification()
    {
        return response()->json(['email_verified' => Auth::user()->hasVerifiedEmail()]);
    }
}
