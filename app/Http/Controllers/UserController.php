<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Hash;



class UserController extends Controller
{   private const PER_PAGE = 15;

    private const SAFE_USER_COLUMNS = [
        'id',
        'name',
        'email',
        'is_active',
        'is_2fa_enabled',
        'created_at',
        'updated_at',
    ];

    private function safeUsersQuery()
    {
        return User::query()
            ->select(self::SAFE_USER_COLUMNS)
            ->with([
                'roles:id,name',
                'roles.permissions:id,name,description',
            ]);
    }

    private function formatUser(User $user): array
    {
        $roles = $user->roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'description' => $permission->description,
                    ];
                })->values()->all(),
            ];
        })->values();

        $permissions = $user->roles
            ->flatMap(function ($role) {
                return $role->permissions;
            })
            ->unique('id')
            ->values()
            ->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'description' => $permission->description,
                ];
            })
            ->all();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => (bool) $user->is_active,
            'is_2fa_enabled' => (bool) $user->is_2fa_enabled,
            'created_at' => optional($user->created_at)->toJSON(),
            'updated_at' => optional($user->updated_at)->toJSON(),
            'roles' => $roles->all(),
            'permissions' => $permissions,
        ];
    }


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
        $users = $this->safeUsersQuery()->paginate($perPage);

        $data = $users->getCollection()->map(function (User $user) {
            return $this->formatUser($user);
        })->all();

        return response()->json([
            'current_page' => $users->currentPage(),
            'data' => $data,
            'first_page_url' => $users->url(1),
            'from' => $users->firstItem(),
            'last_page' => $users->lastPage(),
            'last_page_url' => $users->url($users->lastPage()),
            'links' => [],
            'next_page_url' => $users->nextPageUrl(),
            'path' => $users->path(),
            'per_page' => $users->perPage(),
            'prev_page_url' => $users->previousPageUrl(),
            'to' => $users->lastItem(),
            'total' => $users->total(),
        ]);
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
        $user = $this->safeUsersQuery()->find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($this->formatUser($user), 200);
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



public function getUserPermissions($idUser)
{
    $user = User::find($idUser);
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $permissions = $user->roles
        ->flatMap(function ($role) {
            return $role->permissions;
        })
        ->unique('id')
        ->values()
        ->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'description' => $permission->description,
            ];
        })
        ->all();

    return response()->json(['permissions' => $permissions], 200);
}

}