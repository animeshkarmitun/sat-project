<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Assign a role to a user.
     */
    public function assignRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|uuid|exists:users,user_id',
            'role_id' => 'required|uuid|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->roleService->assignRole($request->user_id, $request->role_id);
            Log::info('Role assigned', ['user_id' => $request->user_id, 'role_id' => $request->role_id]);
            return response()->json(['message' => 'Role assigned successfully'], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Remove a role from a user.
     */
    public function removeRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|uuid|exists:users,user_id',
            'role_id' => 'required|uuid|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->roleService->removeRole($request->user_id, $request->role_id);
            Log::info('Role removed', ['user_id' => $request->user_id, 'role_id' => $request->role_id]);
            return response()->json(['message' => 'Role removed successfully'], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Fetch all roles available in the system.
     */
    public function getAllRoles()
    {
        $roles = $this->roleService->getAllRoles();
        return response()->json(['roles' => $roles], 200);
    }

    /**
     * Fetch roles assigned to a specific user.
     */
    public function getUserRoles($userId)
    {
        $validator = Validator::make(['user_id' => $userId], [
            'user_id' => 'required|uuid|exists:users,user_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $roles = $this->roleService->getUserRoles($userId);
        return response()->json(['roles' => $roles], 200);
    }

    /**
     * Check if a user has a specific role.
     */
    public function userHasRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|uuid|exists:users,user_id',
            'role_id' => 'required|uuid|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $hasRole = $this->roleService->userHasRole($request->user_id, $request->role_id);
        return response()->json(['has_role' => $hasRole], 200);
    }

    /**
     * Get users assigned to a specific role.
     */
    public function getUsersByRole($roleId)
    {
        $validator = Validator::make(['role_id' => $roleId], [
            'role_id' => 'required|uuid|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $users = $this->roleService->getUsersByRole($roleId);
        return response()->json(['users' => $users], 200);
    }
}
