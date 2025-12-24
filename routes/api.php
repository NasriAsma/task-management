<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\update_profile;

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
Route::middleware('auth:sanctum')->post('/toggle-2fa',[update_profile::class,'verif2FA']);
Route::middleware('auth:sanctum')->post('/request-update-code',[update_profile::class,'requestUpdateCode']);
Route::middleware('auth:sanctum')->put('/update-profile',[update_profile::class,'updateUser']);