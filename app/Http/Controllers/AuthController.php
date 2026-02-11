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



class AuthController extends Controller
{



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



public function logout(request $request)
{
  $request->user()->currentAccessToken()->delete();
  return response()->json([
    'message' => ' logged out successfully'
  ]);
}

 

}
