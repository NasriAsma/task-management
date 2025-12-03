<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\user;
use Illuminate\Support\Facades\hash;
use Illuminate\Support\Facades\auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Response;


class AuthController extends Controller
{
public function login( request $request)
{  
    $request->validate([
    'email'=>'required|email',
    'password'=>'required|string'
]);
if (!auth::attempt ($request->only ('email','password')))
  {   
    return response()->json ([
    'message'=>'invalid ', 
   ],401);
}

$user= user::where( 'email',$request->email )-> firstorfail();
$token=$user -> createtoken('auth_token')->plainTextToken;  //création de token
return response()->json ([
  'message'=> 'successfully',
  'user'=>$user,
  'access_token'=>$token,
])
 
;
}

public function register( request $request )
{     $request->validate([
    'name'=>'required|string',
    'email'=>'required|email|unique:users,email',
    'password'=>'required|string|confirmed'
]);
      $user= user ::create ([
    'name'=>$request -> name ,
    'email'=>$request -> email,
    'password'=>hash::make ($request->password) 
    ]);
      return response()->json ([
    'message'=>'user registered successfully',
    'user'=>$user
],201);

}

public function logout(request $request)
{
  $request->user()->currentAccessToken()->delete();
  return response()->json([
    'message' => ' logged out successfully'
  ]);
}
}
