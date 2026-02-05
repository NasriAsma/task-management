<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;


class UserController extends Controller
{
    public function createUser(Request $request)
    {
     
        $valida = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $valida['name'],
            'email' => $valida['email'],
            'password' => bcrypt($valida['password']),
        ]);

        
        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }



public function index(Request $request)
{
    $perPage = (int) $request->query('per_page', 15);
    $users = User::paginate($perPage);
    return response()->json($users);

}


public function updateUser(Request $request, $id)
{
    $user = User::find($id);
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);}

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


public function store(request $request , $id)
 {     $user =user::find($id) ;
     if(!$user){
    return response()->json (['message'=>'user not found'],404);
            }
            else {
        return response()->json ( $user) ;}
 }

public function deleteUser($id)
{
    $user = User::find($id);
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $user->delete();

    return response()->json(['message' => 'User deleted successfully'], 200);
}


public function getUser($id)
{   
    $user = User::find($id);
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    } else {
        return response()->json($user, 200);
    }

}

public function desactiveCompte($id)
{
    $user = User::find($id);
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $user->is_active = false;
    $user->save();

    return response()->json(['message' => 'User account deactivated successfully'], 200);

}

public function activeCompte($id)
{
    $user = User::find($id);
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
   
  $statistique = [  'total_users' =>  $totalUsers = User::count() ,
            'active_users' => $activeUsers = User::where('is_active', true)->count() ,
             'inactive_users' =>  $inactiveUsers = User::where('is_active', false)->count() ];


    return response()->json([
           'statistique' => $statistique,
       ], 200);

}
}