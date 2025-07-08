<?php

use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\CommentContoller;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Auth\AuthController;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function(){
    Route::post('logout', [AuthController::class, 'logout']);
    Route::resource('teams',TeamController::class)->middleware('role:admin|team_owner');
    Route::post('teams/{team}/addMembers', [TeamController::class, 'addMembers'])->middleware('role:admin|team_owner');
    Route::delete('teams/{team}/removeMembers', [TeamController::class, 'removeMembers'])->middleware('role:admin|team_owner');
    Route::post('teams/{team}/transferOwnership/{id}', [TeamController::class, 'changeOwner'])->middleware('role:admin|team_owner');

    Route::resource('projects',ProjectController::class)->middleware('role:admin|team_owner|project_manager');
    Route::post('projects/{project}/addWorkers', [ProjectController::class, 'addWorkers'])->middleware('role:admin|team_owner|project_manager');
    Route::delete('projects/{project}/removeWorkers', [ProjectController::class, 'removeWorkers'])->middleware('role:admin|team_owner|project_manager');
    Route::post('projects/{project}/changeManager/{user}', [ProjectController::class, 'changeManager'])->middleware('role:admin|team_owner');
    Route::post('projects/completedProjects', [ProjectController::class, 'getCompletedProjects'])->middleware('role:admin|team_owner');
    Route::post('projects/CompletedTasksCounts', [ProjectController::class, 'getCompletedTasksCounts'])->middleware('role:admin|team_owner');

    Route::resource('tasks',TaskController::class)->middleware('role:project_manager|member','permission:view tasks');
    Route::post('tasks/{task}/assignTask',[TaskController::class, 'assign']);

    Route::post('/tasks/{task}/comments',[CommentContoller::class , 'storeTaskComment']);
    Route::post('/projects/{project}/comments',[CommentContoller::class , 'storeProjectComment']);
    Route::get('comments', [CommentContoller::class, 'index']);
    Route::get('comments/{comment}', [CommentContoller::class, 'show']);
    Route::patch('comments/{comment}', [CommentContoller::class, 'update']);
    Route::delete('comments/{comment}', [CommentContoller::class, 'destroy']);

    Route::post('/tasks/{attachable}/attachments',[AttachmentController::class , 'store']);
    Route::post('/projects/{attachable}/attachments',[AttachmentController::class , 'store']);
    Route::post('/comments/{attachable}/attachments',[AttachmentController::class , 'store']);
    Route::patch('attachments/{attachment}', [AttachmentController::class, 'update']);
    Route::get('attachments/{attachment}', [AttachmentController::class, 'show']);
    Route::get('attachments', [AttachmentController::class, 'index']);
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy']);

    Route::post('notifications/{notification}/markAsRead', [NotificationController::class, 'markAsRead']);
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/{notification}', [NotificationController::class, 'show']);
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy']);

});
