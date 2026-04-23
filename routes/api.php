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
Route::middleware('auth:sanctum')->get('/audits/user/{userId}', [AuditController::class, 'userAudits']);
Route::middleware('auth:sanctum')->post('/assign-role/{roleName}', [RoleController::class, 'assignRole']);
Route::middleware('auth:sanctum')->get('/has-role/{roleName}', [RoleController::class, 'hasRole']);
Route::middleware('auth:sanctum')->get('/get-roles', [RoleController::class, 'getRoles']);
Route::middleware(['auth:sanctum', 'role:admin,manager', 'permission:view_user'])->get('/users', [UserController::class, 'index']);






Route::middleware(['auth:sanctum', 'role:admin' , ])->group(function () {
    Route::middleware('permission:create_user')->post('/createuser', [UserController::class,'createUser']);
    Route::middleware('permission:update_user')->put('/updateuser/{idUser}', [UserController::class,'updateUser']);
    Route::middleware('permission:delete_user')->delete('/deleteuser/{idUser}', [UserController::class,'deleteUser']);
    Route::middleware('permission:view_user_par_id')->get('/users/{idUser}', [UserController::class,'getUser']);
    Route::middleware('permission:active_compte')->post('/activecompte/{idUser}', [UserController::class, 'activeCompte']);
    Route::middleware('permission:desactive_compte')->put('/desactivecompte/{idUser}', [UserController::class, 'desactiveCompte']);
    Route::middleware('permission:recherche_user')->get('/rechercheuser', [UserController::class, 'store']);
    Route::middleware('permission:view_audits')->get('/audits', [AuditController::class, 'index']);

    Route::middleware('permission:view_permission_by_id')->get('/permissions/{id}', [UserController::class, 'getUserPermissions']);
    Route::middleware('permission:view_all_permission')->get('/permissions', [PermissionController::class, 'index']);
  
    Route::middleware('permission:create_permission')->post('/createpermission/{idUser}', [PermissionController::class, 'store']);
    Route::middleware('permission:delete_permission')->delete('/deletepermission/{id}', [PermissionController::class, 'deletePermission']);
    Route::middleware('permission:update_permission')->put('/updatepermission/{idUser}/{id}', [PermissionController::class, 'updatePermission']);
    Route::middleware('permission:view_statistique_task')->get('/statistiquetask', [TaskController::class, 'getTaskStatistics']);
    Route::middleware('permission:view_statistique_user')->get('/statistiqueuser', [UserController::class, 'getStatistiqueUser']);
    Route::middleware('permission:view_all_Number_of_user')->get('/numberofusers', [UserController::class, 'getNumberofUsers']);
    Route::middleware('permission:view_all_Number_of_task')->get('/numberoftasks', [TaskController::class, 'getTaskCount']);
    Route::middleware('permission:view_Number_of_task_for_user')->get('/numberoftasksforuser/{userId}', [TaskController::class, 'getUserTaskCount']);
   

  
    Route::middleware('permission:assign_role')->post('/assignrole/{roleName}/{id}', [RoleController::class, 'assignRole']);
    Route::middleware('permission:delete_role')->put('/removerole/{roleName}/{id}', [RoleController::class, 'removeRole']);
   
    Route::middleware('permission:assign_task')->post('/assigntask/{taskId}/{userId}', [TaskController::class, 'assignTask']);
    Route::middleware('permission:view_task_details')->get('/viewtaskdetails/{id}', [TaskController::class, 'show']);
    Route::middleware('permission:view_task')->get('/tasks', [TaskController::class, 'index']);
    Route::middleware('permission:view_own_tasks')->get('/mytasks', [TaskController::class, 'index']) ->name('my-tasks');
    Route::middleware('permission:create_task')->post('/tasks', [TaskController::class, 'store']) ->name('create-task');
    Route::middleware('permission:update_task')->put('/tasks/{id}', [TaskController::class, 'update']) ->name('update-task');
    Route::middleware('permission:delete_task')->delete('/tasks/{id}', [TaskController::class, 'destroy']) ->name('delete-task');
});

Route::middleware(['auth:sanctum', 'role:manager'])->group(function () {
    Route::middleware('permission:update_team_task')->put('/teamtasks/{id}', [TaskController::class, 'update'])->name('update-team-task');
    Route::middleware('permission:create_team_task')->post('/teamtasks', [TaskController::class, 'store'])->name('create-team-task');
    Route::middleware('permission:delete_team_task')->delete('/teamtasks/{id}', [TaskController::class, 'destroy'])->name('delete-team-task');
    Route::middleware('permission:view_team_tasks')->get('/teamtasks/{userId}', [TaskController::class, 'viewTeamMemberTasks'])->name('view-team-tasks');
    Route::middleware('permission:view_Number_of_team_tasks_for_user')->get('/teamtasks/{userId}/count', [TaskController::class, 'getUserTaskCount']);
});

Route::middleware(['auth:sanctum', 'role:employee'])->group(function () {
    Route::middleware('permission:view_task_for_user')->get('/tasks/{id}', [TaskController::class, 'show']);
    Route::middleware('permission:update_task_status')->put('/tasks/{id}/status', [TaskController::class, 'updateStatus'])->name('updateStatus');
});






