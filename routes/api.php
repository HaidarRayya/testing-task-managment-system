<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

Route::middleware('security_middleware')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('login', 'login');
        Route::post('logout', 'logout');
        Route::post('refresh', 'refresh');
    });
    Route::middleware('throttle:rate_limit,1')->group(function () {
        Route::prefix('admin')->middleware(['auth:api', 'is_admin'])->group(function () {
            Route::apiResource('roles', RoleController::class);
            Route::get('/deletedRoles', [RoleController::class, 'deletedRoles']);
            Route::post('/roles/{role}/restore', [RoleController::class, 'restoreRole']);
            Route::delete('/roles/{role}/finalDelete', [RoleController::class, 'forceDeleteRole']);
            Route::post('/roles/{role}/addPermissions', [RoleController::class, 'addPermissionToRole']);
            Route::post('/roles/{role}/removePermission', [RoleController::class, 'removePermissionFromRole']);

            Route::apiResource('permissions', PermissionController::class);
            Route::get('/deletedPermissions', [PermissionController::class, 'deletedPermissions']);
            Route::post('/permissions/{permission}/restore', [PermissionController::class, 'restorePermission']);
            Route::delete('/permissions/{permission}/finalDelete', [PermissionController::class, 'forceDeletePermission']);

            Route::apiResource('users', UserController::class);
            Route::get('/deletedUsers', [UserController::class, 'allDeletedUsers']);
            Route::post('/users/{user}/restore', [UserController::class, 'restoreUser']);
            Route::delete('/users/{user}/finalDelete', [UserController::class, 'forceDeleteUser']);

            Route::apiResource('tasks', TaskController::class);
            Route::get('/deletedTasks', [TaskController::class, 'allDeletedTasks']);
            Route::post('/tasks/{task}/restore', [TaskController::class, 'restoreTask']);
            Route::post('/tasks/{task}/assign', [TaskController::class, 'assignEmployee']);
            Route::post('/tasks/{task}/reassign', [TaskController::class, 'reassignEmployee']);
            Route::post('/tasks/{task}/end', [TaskController::class, 'endTask']);

            Route::apiResource('tasks.comments', CommentController::class);
            Route::apiResource('tasks.attachments', AttachmentController::class);
            Route::get('/tasks/{task}/attachments/{attachment}/download', [AttachmentController::class, 'download']);

            Route::get('/dailyReports', [TaskController::class, 'dailyReportsTasks']);
        });

        Route::prefix('developer')->middleware(['auth:api', 'is_developer'])->group(function () {
            Route::apiResource('tasks', TaskController::class)->only(['index', 'show']);
            Route::post('/tasks/{task}/startWork', [TaskController::class, 'startWorkTask']);
            Route::post('/tasks/{task}/endWork', [TaskController::class, 'endWorkTask']);
            Route::apiResource('tasks.comments', CommentController::class)->only(['index', 'show']);
            Route::apiResource('tasks.attachments', AttachmentController::class);
            Route::get('/tasks/{task}/attachments/{attachment}/download', [AttachmentController::class, 'download']);
        });

        Route::prefix('tester')->middleware(['auth:api', 'is_tester'])->group(function () {
            Route::apiResource('tasks', TaskController::class)->only(['index', 'show']);
            Route::post('/tasks/{task}/startTest', [TaskController::class, 'startTestTask']);
            Route::post('/tasks/{task}/endTest', [TaskController::class, 'endTestTask']);
            Route::apiResource('tasks.comments', CommentController::class);
            Route::apiResource('tasks.attachments', AttachmentController::class)->only(['index', 'show']);
            Route::get('/tasks/{task}/attachments/{attachment}/download', [AttachmentController::class, 'download']);
        });
    });
});