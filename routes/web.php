<?php

use Illuminate\Support\Facades\Route;

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

// Route pour le lien de réinitialisation de mot de passe (redirige vers Angular)
Route::get('/password/reset/{token}', function ($token) {
    return redirect('http://localhost:4200/reset-password?token=' . $token . '&email=' . request('email'));
})->name('password.reset');
