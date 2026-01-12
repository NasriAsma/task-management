<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;


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



public function index()
{
    $users = User::all();
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


}
