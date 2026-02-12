<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{   private const PER_PAGE = 15;
    public function createUser(Request $request)
    {
        $this->authorize('create', User::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $perPage = (int) $request->query('per_page', self::PER_PAGE);
        $users = User::paginate($perPage);

        return response()->json($users);
    }

    public function updateUser(Request $request, $idUser)
    {
        $user = User::find($idUser);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $this->authorize('update', $user);

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:8',
        ]);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        }

        $user->update($validatedData);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ], 200);
    }

    public function store(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $this->authorize('delete', $user);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    public function getUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user, 200);
    }

    public function desactiveCompte($idUser)
    {
        $user = User::find($idUser);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->is_active = false;
        $user->save();

        return response()->json(['message' => 'User account deactivated successfully'], 200);
    }

    public function activeCompte($idUser)
    {
        $user = User::find($idUser);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->is_active = true;
        $user->save();

        return response()->json(['message' => 'User account activated successfully'], 200);
    }

    public function getNumberofUsers()
    {
        $count = User::count();
        return response()->json(['number_of_users' => $count], 200);
    }

    public function getStatistiqueUser()
    {
        $this->authorize('viewAny', User::class);

        $statistique = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
        ];

        return response()->json([
            'statistique' => $statistique,
        ], 200);
    }
}