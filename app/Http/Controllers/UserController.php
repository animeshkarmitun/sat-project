<?php
/**
 * Class UserController
 *
 * Manages user profiles, account updates, deletion, and retrieval.
 *
 * API Routes:
 * - GET /user/profile -> getProfile() - Retrieves the authenticated user's profile.
 * - PUT /user/profile -> updateProfile() - Updates the authenticated user's profile.
 * - DELETE /user/delete -> deleteUser() - Soft deletes the authenticated user's account.
 * - POST /user/restore/{userId} -> restoreUser() - Restores a soft-deleted user account (Admin only).
 * - GET /users -> getAllUsers() - Retrieves a list of all users (Admin only).
 *
 * Function Descriptions:
 * - getProfile() - Fetches the profile of the currently authenticated user.
 * - updateProfile(Request $request) - Updates the authenticated user's profile information based on the provided data.
 * - deleteUser() - Soft deletes the currently authenticated user's account.
 * - restoreUser($userId) - Restores a previously soft-deleted user account (Admin only).
 * - getAllUsers() - Retrieves all users in the system, accessible only to administrators.
 */

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\UserService;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Fetch user profile.
     */
    public function getProfile()
    {
        $user = Auth::user();
        return response()->json(['user' => $user], 200);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => "sometimes|email|unique:users,email," . Auth::id() . ",user_id",
            'password' => 'sometimes|string|min:8|confirmed',
            'profile_picture_url' => 'nullable|url|max:2083',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = $this->userService->updateProfile(Auth::user(), $request->all());
            Log::info('User profile updated', ['user_id' => $user->user_id]);
            return response()->json(['message' => 'Profile updated successfully', 'user' => $user], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Delete user account (Soft Delete).
     */
    public function deleteUser()
    {
        $user = Auth::user();

        try {
            $this->userService->deleteUser($user);
            Log::info('User account deleted', ['user_id' => $user->user_id]);
            return response()->json(['message' => 'Account deleted successfully'], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Restore soft-deleted user account.
     */
    public function restoreUser($userId)
    {
        try {
            $user = $this->userService->restoreUser($userId);
            Log::info('User account restored', ['user_id' => $user->user_id]);
            return response()->json(['message' => 'Account restored successfully', 'user' => $user], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Fetch all users (admin only).
     */
    public function getAllUsers()
    {
        $this->authorize('viewAny', User::class);
        $users = $this->userService->getUsers();
        return response()->json(['users' => $users], 200);
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if (!password_verify($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 400);
        }

        try {
            $this->userService->updatePassword($user, $request->new_password);
            Log::info('User password updated', ['user_id' => $user->user_id]);
            return response()->json(['message' => 'Password updated successfully'], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
