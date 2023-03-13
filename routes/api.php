<?php

use App\Http\Controllers\API\AuthController;

use App\Http\Controllers\API\Dashboard\BoardController;
use App\Http\Controllers\API\Dashboard\LabelController;
use App\Http\Controllers\API\Dashboard\ReportController;
use App\Http\Controllers\API\Dashboard\RolesController;
use App\Http\Controllers\API\Dashboard\StatusController;
use App\Http\Controllers\API\Dashboard\TaskController;
use App\Http\Controllers\API\Dashboard\UserController;
use App\Http\Controllers\API\User\TaskController as UserTaskController;
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

Route::prefix('v1')->group(function () {


    Route::post('/token/create', [AuthController::class, 'issue_token']);

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RolesController::class)->only('index');

        Route::post('users/{user}/update-role', [UserController::class, 'updateRole']);
        Route::post('users/import/users-csv', [UserController::class, 'importUsers']);
        Route::get('users/import/users-csv-progress', [UserController::class, 'getImportProgress']);

        Route::apiResource('statuses.tasks', TaskController::class);

        Route::post('statuses/{status}/tasks/{task}/attach-label', [TaskController::class, 'attachLabel']);
        Route::post('statuses/{status}/tasks/{task}/detach-label', [TaskController::class, 'detachLabel']);
        Route::get('statuses/{status}/tasks/{task}/logs', [TaskController::class, 'getLog']);

        Route::apiResource('boards', BoardController::class);
        Route::apiResource('boards.statuses', StatusController::class)->only('index');
        Route::apiResource('labels', LabelController::class);

        Route::get('user/tasks', [UserTaskController::class, 'index']);
        Route::get('user/tasks/expired', [UserTaskController::class, 'getExpiredTasks']);
        Route::post('user/tasks/{task}/update-status', [UserTaskController::class, 'updateTaskStatus']);
        Route::post('user/tasks/{task}/update-assignee', [UserTaskController::class, 'updateTaskAssignee']);
        Route::get('user/tasks/{task}/logs', [UserTaskController::class, 'getTaskLog']);

        Route::get('report/completed-largest-number-of-tasks', [ReportController::class, 'completedLargestNumberOfTasks']);
        Route::get('report/users-with-tasks-that-exceed-due-date', [ReportController::class, 'UsersWithTasksThatExceedDueDate']);

    });
});
