<?php

namespace App\Services;

use App\Models\User;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class UserService
{
    /**
     * Validate user data based on rules.
     *
     * @param array $data
     * @param array $rules
     * @throws CustomException
     */
    private function validateUserData(array $data, array $rules): void
    {
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new CustomException('user.validation_failed', $validator->errors()->toArray(), 422);
        }
    }

    /**
     * Register a new user.
     *
     * @param array $data
     * @return User
     * @throws CustomException
     */
    public function registerUser(array $data): User
    {
        $this->validateUserData($data, [
            'name' => 'required|string|min:3|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
            'role' => 'sometimes|in:student,teacher,admin'
        ]);

        return DB::transaction(function () use ($data) {
            $user = User::create([
                'user_id' => Str::uuid(),
                'name' => trim($data['name']),
                'email' => strtolower(trim($data['email'])),
                'password' => Hash::make($data['password']),
                'role' => $data['role'] ?? 'student',
                'profile_picture_url' => $data['profile_picture_url'] ?? null,
            ]);

            Log::info('New user registered', ['user_id' => $user->id, 'email' => $user->email]);
            return $user;
        });
    }

    /**
     * Authenticate and log in a user.
     *
     * @param array $data
     * @return array
     * @throws CustomException
     */
    public function loginUser(array $data): array
    {
        $this->validateUserData($data, [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string',
        ]);

        // Rate limit login attempts
        $key = 'login_attempts_' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw new CustomException('auth.too_many_attempts', [], 429);
        }

        $user = User::where('email', strtolower(trim($data['email'])))->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($key, 60);
            throw new CustomException('auth.invalid_credentials', [], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        Log::info('User logged in', ['user_id' => $user->id]);
        $user->update(['last_login' => now()]);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout the authenticated user from all devices or current session.
     *
     * @param bool $allSessions
     * @throws CustomException
     */
    public function logoutUser(bool $allSessions = false): void
    {
        $user = Auth::user();
        if (!$user) {
            throw new CustomException('auth.unauthenticated', [], 401);
        }
        if ($allSessions) {
            $user->tokens()->delete();
        } else {
            Auth::user()->currentAccessToken()->delete();
        }
        Log::info('User logged out', ['user_id' => $user->id]);
    }

    /**
     * Force delete a user (Permanent deletion).
     *
     * @param int $userId
     * @throws CustomException
     */
    public function forceDeleteUser(int $userId): void
    {
        $user = User::onlyTrashed()->findOrFail($userId);
        $user->forceDelete();
        Log::info('User permanently deleted', ['user_id' => $user->id]);
    }

    /**
     * Request email change and send verification link.
     *
     * @param int $userId
     * @param string $newEmail
     * @throws CustomException
     */
    public function requestEmailChange(int $userId, string $newEmail): void
    {
        $user = User::findOrFail($userId);

        if ($newEmail === $user->email) {
            throw new CustomException('user.same_email', [], 400);
        }

        // Generate verification token and store temporarily (Pending Implementation of Email Service)
        $verificationToken = Str::random(40);
        Cache::put("email_change_{$user->id}", ['email' => $newEmail, 'token' => $verificationToken], 3600);
        Log::info('Email change requested', ['user_id' => $user->id, 'new_email' => $newEmail]);
        // Email sending logic to be implemented
    }

    /**
     * Confirm email change using verification token.
     *
     * @param int $userId
     * @param string $token
     * @throws CustomException
     */
    public function confirmEmailChange(int $userId, string $token): void
    {
        $cachedData = Cache::get("email_change_{$userId}");
        if (!$cachedData || $cachedData['token'] !== $token) {
            throw new CustomException('user.invalid_email_token', [], 400);
        }

        $user = User::findOrFail($userId);
        $user->update(['email' => $cachedData['email']]);
        Cache::forget("email_change_{$userId}");
        Log::info('User email changed', ['user_id' => $user->id, 'new_email' => $user->email]);
    }
}
