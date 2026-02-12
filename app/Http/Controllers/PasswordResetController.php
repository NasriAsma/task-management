<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;    
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class PasswordResetController extends Controller
{ 

public function sendResetLink(Request $request)
{       
    $request->validate(['email' => 'required|email|exists:users,email']);
    
    $status = Password::sendResetLink($request->only('email'));
    
    return $status === Password::RESET_LINK_SENT 
        ? response()->json(['message' => 'Email envoyé avec succès'], 200)
        : response()->json(['message' => 'Email introuvable'], 422);
}


public function resetPassword(Request $request)
{
    $request->validate([
        'token' => 'required|string',
        'email' => 'required|email|exists:users,email',
        'password' => 'required|string|confirmed'
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user) use ($request) {
            $user->forceFill([
                'password' => Hash::make($request->password),
            ])->save();
        }
    );

    return $status === Password::PASSWORD_RESET
        ? response()->json(['message' => 'Mot de passe réinitialisé avec succès'], 200)
        : response()->json(['message' => 'Token invalide ou expiré'], 422);
}



}



