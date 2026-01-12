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
Route::middleware('auth:sanctum')->post('/toggle-2fa',[update_profile::class,'toggle2FA']);
Route::middleware('auth:sanctum')->post('/request-update-code',[update_profile::class,'requestUpdateCode']);
Route::middleware('auth:sanctum')->put('/update-profile',[update_profile::class,'updateUser']);
Route::middleware('auth:sanctum')->get('/user',[update_profile::class,'getUser']);
Route::middleware('auth:sanctum')->post('/assign-role/{roleName}', [App\Http\Controllers\RoleController::class, 'assignRole']);
Route::middleware('auth:sanctum')->get('/has-role/{roleName}', [App\Http\Controllers\RoleController::class, 'hasRole']);
Route::middleware('auth:sanctum')->get('/get-roles', [App\Http\Controllers\RoleController::class, 'getRoles']);


Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/createUser', [UserController::class,'createUser']);
    Route::get('/users', [UserController::class,'index']);
    Route::put('/updateUser/{id}', [UserController::class,'updateUser']);
    Route::delete('/deleteUser/{id}', [UserController::class,'deleteUser']);
    });
    

