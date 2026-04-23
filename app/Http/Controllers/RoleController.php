<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;

class RoleController extends Controller
{
    public function assignRole(Request $request, $roleName ,$id)
    {
        $this->logRequest($request, __FUNCTION__);
        $user = User::findOrFail($id);

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
    $this->logRequest($request, __FUNCTION__);
    $user = $request->user();
    $hasRole = $user->hasRole($roleName);

    return response()->json([
        'has_role' => $hasRole,
    ], 200);


}
 


public function getRoles(Request $request)
{
    $this->logRequest($request, __FUNCTION__);
    $user = $request->user();
    $roles = $user->roles->pluck('name'); 

    return response()->json([
        'roles' => $roles
    ], 200);
}
 

public function removeRole (Request $request, $roleName, $id )
{
    $this->logRequest($request, __FUNCTION__);
    $user = User::findOrFail($id);

    $role = Role::where('name', $roleName)->first();
    if (!$role) {
        return response()->json(['message' => 'Role not found'], 404);
    }

    $user->roles()->detach($role->id);

    return response()->json([
        'message' => 'Role removed successfully',
        'role' => $roleName,
    ], 200);

}

 




}