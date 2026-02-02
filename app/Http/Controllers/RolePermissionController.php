<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class RolePermissionController extends Controller
{
    /**
     * Create a new role.
     */
    public function createRole(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Validate 'name' and optional 'description', ensuring name is unique on sqlsrv.roles.name
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255', Rule::unique('sqlsrv.roles', 'name')],
            'description' => 'nullable|string',
        ]);

        // Now create the role on sqlsrv
        $role = Role::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        // Log audit if helper exists
        if (function_exists('log_audit')) {
            log_audit(
                'created',
                $role,
                null,
                $role->toArray(),
                'Created new role: ' . $role->name
            );
        }

        return response()->json([
            'message' => 'Role created successfully',
            'role'    => $role,
        ], Response::HTTP_CREATED);
    }

    /**
     * List all roles (with their permissions).
     */
    public function listRoles()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        

        // Eager‐load permissions; both models use sqlsrv
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    /**
     * Update an existing role.
     */
    public function updateRole(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Find role on sqlsrv
        $role = Role::findOrFail($id);

        // Validate input, ensuring new name is unique (excluding this role) on sqlsrv.roles
        $data = $request->validate([
            'name'        => [
                'required',
                'string',
                'max:255',
                Rule::unique('sqlsrv.roles', 'name')->ignore($role->id),
            ],
            'description' => 'nullable|string',
        ]);

        $old = $role->toArray();
        $role->update($data);

        if (function_exists('log_audit')) {
            log_audit(
                'updated',
                $role,
                $old,
                $role->toArray(),
                'Updated role: ' . $role->name
            );
        }

        return response()->json([
            'message' => 'Role updated successfully',
            'role'    => $role->load('permissions'),
        ]);
    }

    /**
     * Delete a role (and detach its permissions).
     */
    public function deleteRole($id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $role = Role::findOrFail($id);

        // Protect certain roles
        if (in_array($role->name, ['super_admin', 'admin'])) {
            return response()->json([
                'error' => 'Cannot delete protected roles like super_admin or admin.'
            ], Response::HTTP_FORBIDDEN);
        }

        $roleData = $role->toArray();
        $role->permissions()->detach(); // Detach pivot entries on sqlsrv
        $role->delete();

        if (function_exists('log_audit')) {
            log_audit(
                'deleted',
                $role,
                $roleData,
                null,
                'Deleted role: ' . $roleData['name']
            );
        }

        return response()->json(['message' => 'Role deleted successfully']);
    }

    /**
     * Delete a permission (and detach from roles).
     */
    public function deletePermission($id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $perm = Permission::findOrFail($id);

        $old = $perm->toArray();
        $perm->roles()->detach(); // Detach pivot entries on sqlsrv
        $perm->delete();

        if (function_exists('log_audit')) {
            log_audit(
                'deleted',
                $perm,
                $old,
                null,
                'Deleted permission: ' . $perm->name
            );
        }

        return response()->json(['message' => 'Permission deleted successfully']);
    }

    /**
     * Create a new permission.
     */
    public function createPermission(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Validate that name is unique on sqlsrv.permissions.name
        $request->validate([
            'name'        => ['required', 'string', Rule::unique('sqlsrv.permissions', 'name')],
            'description' => 'nullable|string',
        ]);

        $permission = Permission::create([
            'name'        => $request->name,
            'description' => $request->description ?? null,
        ]);

        if (function_exists('log_audit')) {
            log_audit(
                'created',
                $permission,
                null,
                $permission->toArray(),
                'Created permission: ' . $permission->name
            );
        }

        return response()->json([
            'message'    => 'Permission created successfully',
            'permission' => $permission,
        ], Response::HTTP_CREATED);
    }

    /**
     * Assign a role to a user (updates user.role_id and user.role_name).
     */
    public function assignRole(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Validate user_id and role_id against sqlsrv tables
        $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('sqlsrv.users', 'id')],
            'role_id' => ['required', 'integer', Rule::exists('sqlsrv.roles', 'id')],
        ]);

        $user = User::findOrFail($request->user_id);
        $old  = $user->toArray();
        $role = Role::findOrFail($request->role_id);

        $user->role_id   = $role->id;
        $user->role_name = $role->name;
        $user->save();

        if (function_exists('log_audit')) {
            log_audit(
                'assigned_role',
                $user,
                $old,
                $user->toArray(),
                'Assigned role "' . $role->name . '" to user'
            );
        }

        return response()->json(['message' => 'Role assigned to user successfully']);
    }

    /**
     * Assign multiple permissions to a role (sync without detaching others).
     */
    public function assignPermissionsToRole(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Validate against sqlsrv.roles and sqlsrv.permissions
        $request->validate([
            'role_id'          => ['required', 'integer', Rule::exists('sqlsrv.roles', 'id')],
            'permission_ids'   => 'required|array',
            'permission_ids.*' => ['integer', Rule::exists('sqlsrv.permissions', 'id')],
        ]);

        $role     = Role::findOrFail($request->role_id);
        $existing = $role->permissions->pluck('id')->toArray();

        // Sync new permissions (without detaching existing ones)
        $role->permissions()->syncWithoutDetaching($request->permission_ids);

        $new = $role->permissions()->pluck('id')->toArray();

        if (function_exists('log_audit')) {
            log_audit(
                'assigned_permissions',
                $role,
                ['permission_ids' => $existing],
                ['permission_ids' => $new],
                'Updated permissions for role: ' . $role->name
            );
        }

        return response()->json(['message' => 'Permissions assigned to role successfully']);
    }

    /**
     * List all permissions.
     */
    public function listPermissions()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // All permissions from sqlsrv
        $permissions = Permission::all();

        return response()->json([
            'permissions' => $permissions,
        ]);
    }
}
