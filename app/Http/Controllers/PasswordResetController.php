<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;    
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class PasswordResetController extends Controller
{ 

/**
 * @OA\Post(
 *     path="/forgot-password",
 *     tags={"Password Reset"},
 *     summary="Demander un lien de réinitialisation",
 *     description="Envoie un email avec un lien de réinitialisation de mot de passe",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email"},
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Email envoyé avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Email envoyé avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Email introuvable",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Email introuvable")
 *         )
 *     )
 * )
 */
public function sendResetLink(Request $request)
{
    $request->validate(['email' => 'required|email|exists:users,email']);
    
    $status = Password::sendResetLink($request->only('email'));
    
    return $status === Password::RESET_LINK_SENT 
        ? response()->json(['message' => 'Email envoyé avec succès'], 200)
        : response()->json(['message' => 'Email introuvable'], 422);
}

/**
 * @OA\Post(
 *     path="/reset-password",
 *     tags={"Password Reset"},
 *     summary="Réinitialiser le mot de passe",
 *     description="Réinitialise le mot de passe avec le token reçu par email",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"token","email","password","password_confirmation"},
 *             @OA\Property(property="token", type="string", example="abc123..."),
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Mot de passe réinitialisé avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Mot de passe réinitialisé avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Token invalide ou expiré",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Token invalide ou expiré")
 *         )
 *     )
 * )
 */
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



