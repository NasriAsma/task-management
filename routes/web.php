<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\RoleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-log', function () {
    // Test avec différents niveaux
    Log::debug('Message de débogage');
    Log::info('Information');
    Log::notice('Notice');
    Log::warning('Avertissement');
    Log::error('Erreur détectée', ['erreur_code' => 'E001']);
    Log::critical('Problème critique', ['serveur' => 'DB']);
    
    return response()->json([
        'status' => 'success',
        'message' => 'Logs écrits ! Vérifiez storage/logs/laravel.log'
    ]);
});

// Route pour le lien de réinitialisation de mot de passe (redirige vers Angular)
Route::get('/password/reset/{token}', function ($token) {
    return redirect('http://localhost:4200/reset-password?token=' . $token . '&email=' . request('email'));
})->name('password.reset');

// Alias web pour assigner un rôle via query string, protégé par Sanctum
Route::middleware('auth:sanctum')->get('/assign-role', [RoleController::class, 'assignRole']);
