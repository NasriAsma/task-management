<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\update_profile;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\tasksController;
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


Route:: post ('/register',[AuthController::class,'register']);
Route:: post ('/login',[AuthController::class,'login']);

Route ::post( '/forgot-password',[PasswordResetController::class,'sendResetLink']);
Route ::post ('/reset-password',[PasswordResetController::class,'resetPassword']);

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
    Route::middleware('permission:create_user')->post('/createUser', [UserController::class,'createUser']);
    Route::middleware('permission:update_user')->put('/updateUser/{id}', [UserController::class,'updateUser']);
    Route::middleware('permission:delete_user')->delete('/deleteUser/{id}', [UserController::class,'deleteUser']);
    Route::middleware('permission:view_user')->get('/users', [UserController::class,'index']);
    Route::middleware('permission:view_user_par_id')->get('/user/{id}', [UserController::class,'getUser']);
    Route::middleware('permission:active_compte')->post('/active_compte/{id}', [UserController::class, 'activeCompte']);
    Route::middleware('permission:desactive_compte')->put('/desactive_compte/{id}', [UserController::class, 'desactiveCompte']);

    Route::middleware(['permission:view_audits', 'api.audit'])->get('/user/{user}/audits', [AuditController::class, 'getUserAudits']);


    Route::middleware('permission:view_all_permission')->get('/permissions', [PermissionController::class, 'view_all_permissions']);
    Route::middleware('permission:create_permission')->post('/create_permission', [PermissionController::class, 'view_own_tasks']);
    Route::middleware('permission:delete_permission')->delete('/delete_permission/{id}', [PermissionController::class, 'deletePermission']);
    Route::middleware('permission:update_permission')->put('/update_permission', [PermissionController::class, 'store']);
    Route::middleware('permission:view_statistique_task')->get('/statistique-task', [TasksController::class, 'getStatistiquetask']);
    Route::middleware('permission:view_statistique_user')->get('/statistique-user', [UserController::class, 'getStatistiqueUser']);
    Route::middleware('permission:view_all_Number_of_user')->get('/number-of-users', [UserController::class, 'getNumberTasksPerUser']);
    Route::middleware('permission:view_all_Number_of_task')->get('/number-of-tasks', [TasksController::class, 'getNumberGlobaleofTasks']);
    Route::middleware('permission:view_Number_of_task_for_user')->get('/number-of-tasks-for-user/{userId}', [TasksController::class, 'getNumberTaskforUser']);
   

  
    Route::middleware('permission:assign_role')->post('/assign-role/{roleName}/{id}', [RoleController::class, 'assignRole']);
    Route::middleware('permission:delete_role')->put('/remove-role/{roleName}/{id}', [RoleController::class, 'removeRole']);
   
    Route::middleware('permission:assign_task')->post('/assign-task/{taskId}/{userId}', [tasksController::class, 'assignTask']);
    Route::middleware('permission:view_task_details')->get('/view_task_details/{id}', [tasksController::class, 'view_task_details']);
    Route::middleware('permission:view_task')->get('/get-tasks', [tasksController::class, 'view_all_tasks']);
    Route::middleware('permission:view_own_tasks')->get('/view_own_tasks/{id}', [tasksController::class, 'view_own_tasks']);
    Route::middleware('permission:create_task')->post('/create-task/{id}', [tasksController::class, 'store']);
    Route::middleware('permission:update_task')->put('/update-task/{id}', [tasksController::class, 'update']);
    Route::middleware('permission:delete_task')->delete('/delete-task/{id}', [tasksController::class, 'destroy_any_task']);   
    });





Route::middleware(['auth:sanctum', 'role:manager' ])->group(function () {
Route::middleware('permission:update_team_task')->put('/update-team-task/{userId}/{id}', [tasksController::class, 'update_team_task']);
Route::middleware('permission:view_user')->get('/users', [UserController::class,'index']);
Route::middleware('permission:create_team_task')->post('/create-team-task/{userId}/{id}', [tasksController::class, 'create_team_task']);
Route::middleware('permission:delete_team_task')->delete('/delete-team-task/{userId}/{id}', [tasksController::class, 'delete_team_task']);
Route::middleware('permission:view_team_tasks')->get('/team-tasks/{userId}', [TasksController::class, 'viewTeamMemberTasks']);  
Route::middleware('permission:view_Number_of_team_tasks_for_user')->get('/count-team-tasks/{userId}', [TasksController::class, 'getNumberTaskTeamforUser']);


});


Route::middleware(['auth:sanctum', 'role:employe' ])->group(function () {
Route::middleware('permission:view_task_for_user')->get('/view_task_for_user/{userId}/{taskId}', [tasksController::class, 'view_task_for_user']);
Route::middleware('permission:update_task_status')->put('/update_task_status/{id}', [tasksController::class, 'update_task_status']);
});






