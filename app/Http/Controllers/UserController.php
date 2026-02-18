<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Hash;



class UserController extends Controller
{   private const PER_PAGE = 15;


    public function createUser(UserRequest  $request)
    {
      
    $this->authorize('create', User::class);
    
    $validated = $request->validated();
    $validated['password'] = bcrypt($validated['password']);

    $user = User::create($validated);

    return response()->json(['message' => 'User created', 'user' => $user], 201);
}


    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $perPage = (int) $request->query('per_page', self::PER_PAGE);
        $users = User::paginate($perPage);

        return response()->json($users);
    }

    public function updateUser(UserRequest $request, $idUser)
    {
    $user = User::findOrFail($idUser);
    $this->authorize('update', $user);

    $validatedData = $request->validated();

    if (isset($validatedData['password'])) {
        $validatedData['password'] = Hash::make($validatedData['password']);
    }
    if (isset($validatedData['email'])) {
        $validatedData['email'] = $validatedData['email'];
    }

    if (isset($validatedData['name'])) {
        $validatedData['name'] = $validatedData['name'];
    }
    $user->update($validatedData);
    return response()->json(['message' => 'User updated', 'user' => $user]);

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