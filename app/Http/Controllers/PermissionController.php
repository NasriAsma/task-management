<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Role;



class PermissionController extends Controller
{  


	public function index()
	{
		return response()->json(Permission::all());
	}
	


	public function store(Request $request , $id)
	{
		$data = $request->validate([
			'name' => 'required|string|max:255|unique:permissions,name',
			'description' => 'nullable|string|max:255',
		]);
        $user = User::find($id);
		$permission = $user->Permission::create($data);

		return response()->json([
			'message' => 'Permission created successfully',
			'permission' => $permission,
		], 201);
	}

	public function givePermissionToRole($roleName, $permissionName)
	{
		$role = Role::where('name', $roleName)->first();
		if (!$role) {
			return response()->json(['message' => 'Role not found'], 404);
		}

		$permission = Permission::where('name', $permissionName)->first();
		if (!$permission) {
			return response()->json(['message' => 'Permission not found'], 404);
		}

		$role->permissions()->syncWithoutDetaching([$permission->id]);

		return response()->json([
			'message' => 'Permission assigned to role successfully',
			'role' => $roleName,
			'permission' => $permissionName,
		], 200);
	}

public function deletePermission(Request $request, $id)
{      $permission =permission::findorfail($id);
	 if($permission){
		$permission->delete();
		return response()->json(['message'=>'Permission deleted successfully'],200); }
}






}
