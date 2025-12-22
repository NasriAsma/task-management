<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Use App\Http\Controllers\AuthController;
Use App\Http\Controllers\PasswordResetController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route:: post ('/register',[AuthController::class,'register']);
Route:: post ('/login',[AuthController::class,'login']);

Route ::post( '/forgot-password',[PasswordResetController::class,'sendResetLink']);
Route ::post ('/reset-password',[PasswordResetController::class,'resetPassword']);

Route::post('/verify-2fa', [AuthController::class, 'verify2fa']);


Route::middleware('auth:sanctum')->post('/logout',[AuthController::class,'logout']);
