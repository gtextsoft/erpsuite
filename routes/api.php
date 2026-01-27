<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\UserController;
use Modules\Taskly\Http\Controllers\Api\ProjectApiController;
use Modules\Hrm\Http\Controllers\Api\AttendanceApiController;
use  Modules\ToDo\Http\Controllers\API\ToDoAPIController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Route::prefix('{module}')->group(function () {

//     Route::post('/login',[AuthApiController::class,'login']);
//     Route::post('/logout',[AuthApiController::class,'logout'])->middleware('jwt.api.auth');
//     Route::post('/refresh',[AuthApiController::class,'refresh'])->middleware('jwt.api.auth');
//     Route::post('/edit-profile',[AuthApiController::class,'editProfile'])->middleware('jwt.api.auth');
//     Route::post('/change-password',[AuthApiController::class,'changePassword'])->middleware('jwt.api.auth');
//     Route::post('/delete-account',[AuthApiController::class,'deleteAccount'])->middleware('jwt.api.auth');
//     Route::post('get-workspace-users',[AuthApiController::class,'getWorkspaceUsers'])->middleware('jwt.api.auth');

// });


////////////////////////////////////////////////SYTEMAP APIs////////////////////////////////////////////////////


Route::group(['prefix' => 'sytemap'], function() {
    Route::post('/client/push', [UserController::class, 'syteMapIntegration'])->middleware('api.key');
    Route::post('/clientInvoice/push', [UserController::class, 'syteMapUserInvoice'])->middleware('api.key');
    Route::post('/clientPayment/push', [UserController::class, 'syteMapUserPayment'])->middleware('api.key');
});


////////////////////////////////////////////////USER AUTH APIs/////////////////////////////////////////////////

Route::group(['prefix' => 'mobile'], function () {
    Route::post('/login',[AuthApiController::class,'login']);
    Route::post('/logout',[AuthApiController::class,'logout'])->middleware('jwt.api.auth');
    Route::post('/refresh',[AuthApiController::class,'refresh'])->middleware('jwt.api.auth');
    Route::post('/edit-profile',[AuthApiController::class,'editProfile'])->middleware('jwt.api.auth');
    Route::post('/change-password',[AuthApiController::class,'changePassword']);
    Route::post('/delete-account',[AuthApiController::class,'deleteAccount'])->middleware('jwt.api.auth');
    Route::post('get-workspace-users',[AuthApiController::class,'getWorkspaceUsers'])->middleware('jwt.api.auth');
    // Route::get('/user/{id?}', [UserController::class, 'getUserArray'])->middleware('jwt.api.auth');
});


////////////////////////////////////////////////PROJECT APIs/////////////////////////////////////////////////

Route::group(['prefix' => 'projects'], function () {
    Route::get('/get-projects', [ProjectApiController::class, 'index'])->middleware('jwt.api.auth'); // GET all projects
    Route::post('/create-project', [ProjectApiController::class, 'projectCreateAndUpdate'])->middleware('jwt.api.auth'); // POST create a project
    Route::post('/update-project', [ProjectApiController::class, 'projectCreateAndUpdate'])->middleware('jwt.api.auth'); // POST Update a project
    Route::post('/status', [ProjectApiController::class, 'projectStatusUpdate'])->middleware('jwt.api.auth'); // Update Project Status
    Route::delete('/delete-project', [ProjectApiController::class, 'destroyProject'])->middleware('jwt.api.auth'); // DELETE a project
    Route::get('/details', [ProjectApiController::class, 'projectDetails'])->middleware('jwt.api.auth');
    Route::get('/workspace/users', [ProjectApiController::class, 'getWorkspaceUsers'])->middleware('jwt.api.auth');
    Route::post('/activity', [ProjectApiController::class, 'projectActivity'])->middleware('jwt.api.auth');
});

Route::post('/users/create-from-investments', [UserController::class, 'createUsersFromInvestments'])->name('users.create-from-investments')->middleware('jwt.api.auth');
Route::post('/users/create-from-clients', [UserController::class, 'createUsersFromClients'])->name('users.create-from-clients')->middleware('jwt.api.auth');
Route::post('/verify-user', [UserController::class, 'verifyUserByEmail'])->middleware('api.key')->name('api.verify-user');

//Route::post('/verify-user', [UserController::class, 'verifyUserByEmail'])->middleware('gtextai.api.key')->name('api.verify-user');



////////////////////////////////////////////////CLOCK IN CLOCK OUT API/////////////////////////////////////////////////

Route::group(['prefix' => 'attendance'], function () {
    Route::post('/clock', [AttendanceApiController::class, 'clockInOut'])->middleware('jwt.api.auth');
    Route::get('/status/{user_id}', [AttendanceApiController::class, 'checkClockStatus'])->middleware('jwt.api.auth');

});


////////////////////////////////////////////////TODO API/////////////////////////////////////////////////

Route::group(['prefix' => 'todos'], function () {
     // ToDo List Routes
    Route::get('/', [ToDoAPIController::class, 'index'])->middleware('jwt.api.auth');
    Route::get('/board', [ToDoAPIController::class, 'board'])->middleware('jwt.api.auth');
    Route::get('/calendar', [ToDoAPIController::class, 'calendar'])->middleware('jwt.api.auth');
    Route::post('/', [ToDoAPIController::class, 'store'])->middleware('jwt.api.auth');
    Route::get('/{id}', [ToDoAPIController::class, 'show'])->middleware('jwt.api.auth');
    Route::put('/{id}', [ToDoAPIController::class, 'update'])->middleware('jwt.api.auth');
    Route::delete('/{id}', [ToDoAPIController::class, 'destroy'])->middleware('jwt.api.auth');
    Route::get('/modules', [ToDoAPIController::class, 'getModules'])->middleware('jwt.api.auth');
    
    // ToDo Status Management
    Route::post('/{id}/status-update', [ToDoAPIController::class, 'statusUpdate'])->middleware('jwt.api.auth');
    Route::put('/task-order', [ToDoAPIController::class, 'taskOrderUpdate'])->middleware('jwt.api.auth');
    
    
    // ToDo Description Management
    Route::get('/{id}/description', [ToDoAPIController::class, 'description'])->middleware('jwt.api.auth');
    Route::put('/{id}/description-status', [ToDoAPIController::class, 'updateDescriptionStatus'])->middleware('jwt.api.auth');
});





