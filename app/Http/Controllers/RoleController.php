<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;

class RoleController extends Controller
{
    public function assignRole(Request $request, $roleName = null)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $roleName = $roleName ?? $request->query('roleName');
        if (!$roleName) {
            return response()->json(['message' => 'roleName is required'], 422);
        }

        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $user->roles()->syncWithoutDetaching([$role->id]);

        return response()->json([
            'message' => 'Role assigned successfully',
            'role' => $roleName,
        ], 200);
    }

    public function hasRole(Request $request, $roleName)
{
    $user = $request->user();
    $hasRole = $user->hasRole($roleName);

    return response()->json([
        'has_role' => $hasRole,
    ], 200);


}
 


public function getRoles(Request $request)
{
    $user = $request->user();
    $roles = $user->roles->pluck('name'); 

    return response()->json([
        'roles' => $roles
    ], 200);
}


}
