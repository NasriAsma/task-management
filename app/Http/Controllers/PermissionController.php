<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;



class PermissionController extends Controller
{  


	public function index(Request $request)
	{
		$perPage = (int) $request->query('per_page', 15);
		return response()->json(Permission::paginate($perPage));
	}
	
    public function viewPermissionById($id)
	{
		$permission = Permission::findOrFail($id);
		return response()->json($permission);
	}

	public function store(Request $request, $idUser)
	{
		User::findOrFail($idUser);

		$data = $request->validate([
			'name' => 'required|string|max:255|unique:permissions,name',
			'description' => 'nullable|string|max:255',
		]);

		$permission = Permission::create($data);

		return response()->json([
			'message' => 'Permission created successfully',
			'permission' => $permission,
		], 201);
	}

	public function updatePermission(Request $request, $idUser, $id)
	{
		User::findOrFail($idUser);
		$permission = Permission::findOrFail($id);

		$data = $request->validate([
			'name' => 'sometimes|string|max:255|unique:permissions,name,' . $permission->id,
			'description' => 'sometimes|nullable|string|max:255',
		]);

		$permission->update($data);

		return response()->json([
			'message' => 'Permission updated successfully',
			'permission' => $permission,
		]);
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
{
	$permission = Permission::findOrFail($id);
	$permission->delete();

	return response()->json(['message' => 'Permission deleted successfully'], 200);
}






}
