<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\user;
use Illuminate\Support\Facades\hash;
use Illuminate\Support\Facades\auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Response;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use OpenApi\Annotations\Info;

/**
 * @OA\Info(
 *     title="Task Management API",
 *     version="1.0.0",
 *     description="API pour la gestion des tâches et l'authentification"
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Serveur de développement"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class AuthController extends Controller
{


/**
 * @OA\Post(
 *     path="/login",
 *     tags={"Authentication"},
 *     summary="Login API",
 *     description="Login to get access token",
 *     @OA\RequestBody(
 *      
 *         @OA\JsonContent(
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="successfully"),
 *             @OA\Property(property="user", type="object"),
 *             @OA\Property(property="access_token", type="string", example="1|token...")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated"
 *     )
 * )
 */
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



/**
 * @OA\Post(
 *     path="/register",
 *     tags={"Authentication"},
 *     summary="Register API",
 *     description="Register to create a new user",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","email","password","password_confirmation"},
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="user registered successfully"),
 *             @OA\Property(property="user", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     )
 * )
 */
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


/**
 * @OA\Post(
 *     path="/logout",
 *     tags={"Authentication"},
 *     summary="Logout API",
 *     description="Logout to revoke the access token",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="logged out successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated"
 *     )
 * )
 */

public function logout(request $request)
{
  $request->user()->currentAccessToken()->delete();
  return response()->json([
    'message' => ' logged out successfully'
  ]);
}
}
