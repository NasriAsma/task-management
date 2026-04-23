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
    

class ProfileController extends Controller
{    

    public function toggle2FA(Request $request)
    {
        $this->logRequest($request, __FUNCTION__);
        $user = $request->user();
        if ($request->has('is_2fa_enabled')) {
            $request->validate(['is_2fa_enabled' => 'required|boolean']);
            $user->is_2fa_enabled = (bool) $request->is_2fa_enabled;     
            //recupère la valeur 0,1 de la valeur is_2fa_enabled ET CONVERTIR en booleen 
            //puis stocker dans la colonne is_2fa_enabled 

        } else {   //SI MON requete vide alors on desactive par inverser la valeur actuelle
            $user->is_2fa_enabled = !$user->is_2fa_enabled; 

        }
         if (!$user->is_2fa_enabled) {            
            $user->code_2FA = null;
            $user->code_2FA_expiry = null;
        }

        $user->save();

        return response()->json([
            'message' => '2FA mise à jour',
            'is_2fa_enabled' => $user->is_2fa_enabled,
        ], 200);
   
    }  





public function requestUpdateCode(Request $request)
{  $user =$request->user();
    $this->logRequest($request, __FUNCTION__);
    // générer un code 2FA
 $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $user->code_2FA = $code;
    $user->code_2FA_expiry = now()->addMinutes(5);
    $user->save();

   // envoyer le code par email 
   /* Mail::raw("Votre code de vérification pour modifier votre profil : {$code}", function ($message) use ($user) {
        $message->to($user->email)->subject('Code de vérification');
    }); */
    Log::info("Code 2FA de modification de profil : {$code} pour {$user->email}");

    return response()->json([
        'message' => 'Code 2FA envoyé par email'
    ], 200);


}





public function updateUser(Request $request)
{   
    $this->logRequest($request, __FUNCTION__);
    $user = $request->user();

    $is_sensitive = $request->filled('email') || $request->filled('password');
    
    if ($is_sensitive) {
      

        if (!$user->code_2FA || !$user->code_2FA_expiry) {
            return response()->json([
                'message' => 'Veuillez d\'abord demander un code de verification'
            ], 422);
        }

        if ($request->input('code_2fa') !== $user->code_2FA) {
            return response()->json([
                'message' => 'Le code 2FA est incorrect'
            ], 422);
        }
    }

    $rules = [
        'name' => 'sometimes|string',
        'email' => 'sometimes|email|unique:users,email,' . $user->id,
        'is_2fa_enabled' => 'sometimes|boolean',
    ];
    
    if ($request->filled('password')) {
        $rules['password'] = 'required|string|confirmed|min:6';
    }

    $validated = $request->validate($rules);

    if (isset($validated['name'])) {
        $user->name = $validated['name'];
    }
    if (isset($validated['email'])) {
        $user->email = $validated['email'];
    }
    if (isset($validated['is_2fa_enabled'])) {
        $user->is_2fa_enabled = $validated['is_2fa_enabled'];
    }
    if (isset($validated['password'])) {
        $user->password = bcrypt($validated['password']);
    }

    if ($is_sensitive) {
        $user->code_2FA = null;
        $user->code_2FA_expiry = null;
    }
    
    $user->save();

    return response()->json([
        'message' => 'Profil mis à jour avec succès',
        'user' => $user,
    ], 200);
}


public function getUser(Request $request)
{
    $this->logRequest($request, __FUNCTION__);
    $user = $request->user();

    return response()->json([
        'user' => $user,
    ], 200);
}





}