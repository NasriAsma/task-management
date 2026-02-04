<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Response;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use OpenApi\Annotations\Info;
use App\Mail\testmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


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
public function login(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['message' => 'invalid'], 401);
    }

    $user = User::where('email', $request->email)->firstOrFail();

    // // Si 2FA n'est pas activée, on renvoie directement le token
    // if ($user->is_2fa_enabled==0) {
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message'      => 'successfully',
            'user'         => $user,
            'access_token' => $token,
        ], 200);
    // }

    // 2FA activée: générer et envoyer le code, puis exiger vérification
    /* $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $user->code_2FA = $code;
    $user->code_2FA_expiry = now()->addMinutes(5);
    $user->save();

    Mail::raw("Votre code 2FA est : {$code}", function ($message) use ($user) {
        $message->to($user->email)->subject('Code 2FA');
    });

    Auth::logout();

    return response()->json([
        'requires_2fa' => true,
        'message'      => 'Code envoyé par email'
    ], 200); */

    // Pour le test : Log au lieu de Mail
    /*
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $user->code_2FA = $code;
    $user->code_2FA_expiry = now()->addMinutes(5);
    $user->save();
    Log::info("Code 2FA Login : {$code} pour {$user->email}");
    Auth::logout();
    return response()->json(['requires_2fa' => true, 'message' => 'Code envoyé (voir logs)'], 200);
    */
}


public function verify2fa(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'code'  => 'required|string'
    ]);

    $user = User::where('email', $request->email)->first();
    if (!$user || !$user->code_2FA || !$user->code_2FA_expiry) {
        return response()->json(['message' => 'session expired'], 401);
    }

    if ($user->code_2FA !== $request->code || now()->greaterThan($user->code_2FA_expiry)) {
        return response()->json(['message' => 'invalid 2FA code'], 422);
    }

    // Code valide : on nettoie et on délivre le token
    $user->forceFill([
        'code_2FA'         => null,
        'code_2FA_expiry'  => null,
    ])->save();

    $token = $user->createToken('auth_token')->plainTextToken;
    return response()->json([
        'message'      => 'successfully',
        'user'         => $user,
        'access_token' => $token,
    ], 200);
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
    'password'=>'required|string|confirmed|min:6'
]);
      $user= User ::create ([
    'name'=>$request -> name ,
    'email'=>$request -> email,
    'password'=>Hash::make ($request->password) 
    ]);

// try {
//     Mail::to($user->email)->send(new testmail());
// } catch (\Exception $e) {
//     // Log error or ignore if mail fails, but don't block registration
// }
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
