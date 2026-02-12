<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ProfileController;

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


Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);

Route::post('/forgot-password',[PasswordResetController::class,'sendResetLink']);
Route::post('/reset-password',[PasswordResetController::class,'resetPassword']);

Route::post('/verify-2fa', [AuthController::class, 'verify2fa']);


Route::middleware('auth:sanctum')->post('/logout',[AuthController::class,'logout']);
Route::middleware('auth:sanctum')->post('/toggle-2fa',[ProfileController::class,'toggle2FA']);
Route::middleware('auth:sanctum')->post('/request-update-code',[ProfileController::class,'requestUpdateCode']);
Route::middleware('auth:sanctum')->put('/update-profile',[ProfileController::class,'updateUser']);
Route::middleware('auth:sanctum')->get('/user',[ProfileController::class,'getUser']);
Route::middleware('auth:sanctum')->post('/assign-role/{roleName}', [RoleController::class, 'assignRole']);
Route::middleware('auth:sanctum')->get('/has-role/{roleName}', [RoleController::class, 'hasRole']);
Route::middleware('auth:sanctum')->get('/get-roles', [RoleController::class, 'getRoles']);






Route::middleware(['auth:sanctum', 'role:admin' , ])->group(function () {
    Route::middleware('permission:create_user')->post('/create-user', [UserController::class,'createUser']);
    Route::middleware('permission:update_user')->put('/update-user/{id}', [UserController::class,'updateUser']);
    Route::middleware('permission:delete_user')->delete('/delete-user/{id}', [UserController::class,'deleteUser']);
    Route::middleware('permission:view_user')->get('/users', [UserController::class,'index']);
    Route::middleware('permission:view_user_par_id')->get('/users/{id}', [UserController::class,'getUser']);
    Route::middleware('permission:active_compte')->post('/active-compte/{id}', [UserController::class, 'activeCompte']);
    Route::middleware('permission:desactive_compte')->put('/desactive-compte/{id}', [UserController::class, 'desactiveCompte']);

    Route::middleware(['permission:view_audits', 'api.audit'])->get('/users/{user}/audits', [AuditController::class, 'getUserAudits']);


    Route::middleware('permission:view_all_permission')->get('/permissions', [PermissionController::class, 'index']);
    Route::middleware('permission:create_permission')->post('/create-permission/{idUser}', [PermissionController::class, 'store']);
    Route::middleware('permission:delete_permission')->delete('/delete-permission/{id}', [PermissionController::class, 'deletePermission']);
    Route::middleware('permission:update_permission')->put('/update-permission/{idUser}/{id}', [PermissionController::class, 'updatePermission']);
    Route::middleware('permission:view_statistique_task')->get('/statistique-task', [TaskController::class, 'getTaskStatistics']);
    Route::middleware('permission:view_statistique_user')->get('/statistique-user', [UserController::class, 'getStatistiqueUser']);
    Route::middleware('permission:view_all_Number_of_user')->get('/number-of-users', [UserController::class, 'getNumberofUsers']);
    Route::middleware('permission:view_all_Number_of_task')->get('/number-of-tasks', [TaskController::class, 'getTaskCount']);
    Route::middleware('permission:view_Number_of_task_for_user')->get('/number-of-tasks-for-user/{userId}', [TaskController::class, 'getUserTaskCount']);
   

  
    Route::middleware('permission:assign_role')->post('/assign-role/{roleName}/{id}', [RoleController::class, 'assignRole']);
    Route::middleware('permission:delete_role')->put('/remove-role/{roleName}/{id}', [RoleController::class, 'removeRole']);
   
    Route::middleware('permission:assign_task')->post('/assign-task/{taskId}/{userId}', [TaskController::class, 'assignTask']);
    Route::middleware('permission:view_task_details')->get('/view-task-details/{id}', [TaskController::class, 'show']);
    Route::middleware('permission:view_task')->get('/tasks', [TaskController::class, 'index']);
    Route::middleware('permission:view_own_tasks')->get('/my-tasks', [TaskController::class, 'index']);
    Route::middleware('permission:create_task')->post('/tasks', [TaskController::class, 'store']);
    Route::middleware('permission:update_task')->put('/tasks/{id}', [TaskController::class, 'update']);
    Route::middleware('permission:delete_task')->delete('/tasks/{id}', [TaskController::class, 'destroy']);   
    });





Route::middleware(['auth:sanctum', 'role:manager' ])->group(function () {
Route::middleware('permission:update_team_task')->put('/team-tasks/{id}', [TaskController::class, 'update']);
Route::middleware('permission:view_user')->get('/users', [UserController::class,'index']);
Route::middleware('permission:create_team_task')->post('/team-tasks', [TaskController::class, 'store']);
Route::middleware('permission:delete_team_task')->delete('/team-tasks/{id}', [TaskController::class, 'destroy']);
Route::middleware('permission:view_team_tasks')->get('/team-tasks/{userId}', [TaskController::class, 'viewTeamMemberTasks']);  
Route::middleware('permission:view_Number_of_team_tasks_for_user')->get('/team-tasks/{userId}/count', [TaskController::class, 'getUserTaskCount']);


});


Route::middleware(['auth:sanctum', 'role:employe' ])->group(function () {
Route::middleware('permission:view_task_for_user')->get('/tasks/{id}', [TaskController::class, 'show']);
Route::middleware('permission:update_task_status')->put('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
});






