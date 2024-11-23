<?php

use App\Http\Controllers\api\GetDataByUser;
use App\Http\Controllers\api\ProjectController;
use App\Http\Controllers\api\ProjectUserController;
use App\Http\Controllers\api\TaskController;
use App\Http\Controllers\api\TaskUserController;
use App\Http\Controllers\api\TodoListController;
use App\Http\Controllers\AuthController;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


Route::middleware('auth:sanctum')->group(function () {

    // get user by email (search by partial email)
    Route::get('/user/search/{email}', function ($email) {
        $users = User::where('email', 'like', '%' . $email . '%')->get();

        $response = [];
        foreach ($users as $user) {
            $response[] = [
                'id' => $user->id,
                'email' => $user->email
            ];
        }

        return response()->json($response);
    });



    // Routes Project
    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::post('/', [ProjectController::class, 'store']);
        Route::get('/{projectId}', [ProjectController::class, 'show']);
        Route::put('/{projectId}', [ProjectController::class, 'update']);
        Route::delete('/{projectId}', [ProjectController::class, 'destroy']);
        Route::get('/{projectId}/assignUser', [ProjectController::class, 'showProjectUsers']);
        Route::post('/{projectId}/updateimage', [ProjectController::class, 'updateImage']);

        Route::get('/user/{iduser}', [GetDataByUser::class, 'getProjectsByUser']);
        Route::get('/{idproject}/todolists', [GetDataByUser::class, 'getTodoListsByProject']);
        Route::get('/todolists/{idtodolist}/tasks', [GetDataByUser::class, 'getTasksByTodoList']);
        Route::get('/tasks/user/{userId}', [GetDataByUser::class, 'getTasksByUser']);

    });

    // Routes TodoList dalam Project
    Route::prefix('projects/{projectId}/todoLists')->group(function () {
        Route::get('/', [TodoListController::class, 'index']);
        Route::post('/', [TodoListController::class, 'store']);
        Route::get('/{todoListId}', [TodoListController::class, 'show']);
        Route::put('/{todoListId}', [TodoListController::class, 'update']);
        Route::delete('/{todoListId}', [TodoListController::class, 'destroy']);
    });

    // Routes Task dalam TodoList
    Route::prefix('projects/{projectId}/todoLists/{todoListId}/tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('{taskId}', [TaskController::class, 'show']);
        Route::put('{taskId}', [TaskController::class, 'update']);
        Route::delete('{taskId}', [TaskController::class, 'destroy']);
    });


    Route::prefix('setting_user_project')->group(function () {
        Route::get('/{projectId}/assignUser', [ProjectUserController::class, 'index']);
        Route::post('/{projectId}/assignUser/{userId}', [ProjectUserController::class, 'assignUserToProject']);
        Route::post('/{projectId}/unassignUser/{userId}', [ProjectUserController::class, 'unassignUserFromProject']);
    });

    Route::prefix('setting_user_task')->group(function () {
        Route::get('/{projectId}/assignUser', [TaskUserController::class, 'index']);
        Route::post('/{projectId}/tasks/{taskId}/assignUser/{userId}', [TaskUserController::class, 'assignUserToTask']);
        Route::post('/{taskId}/unassignUser/{userId}', [TaskUserController::class, 'unassignUserFromTask']);
    });
});





































    // // Routes Project
    // Route::get('/projects', [ProjectController::class, 'index']);
    // Route::post('/projects', [ProjectController::class, 'store']);
    // Route::get('/projects/{project}', [ProjectController::class, 'show']);
    // Route::put('/projects/{project}', [ProjectController::class, 'update']);
    // Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

    // // Routes TodoList dalam Project
    // Route::get('/projects/{project}/todo-lists', [TodoListController::class, 'index']);
    // Route::post('/projects/{project}/todo-lists', [TodoListController::class, 'store']);
    // Route::get('/projects/{project}/todo-lists/{todoList}', [TodoListController::class, 'show']);
    // Route::put('/projects/{project}/todo-lists/{todoList}', [TodoListController::class, 'update']);
    // Route::delete('/projects/{project}/todo-lists/{todoList}', [TodoListController::class, 'destroy']);

    // // Routes Task dalam TodoList
    // Route::get('projects/{projectId}/todoLists/{todoListId}/tasks', [TaskController::class, 'index']);
    // Route::post('projects/{projectId}/todoLists/{todoListId}/tasks', [TaskController::class, 'store']);
    // Route::get('projects/{projectId}/todoLists/{todoListId}/tasks/{taskId}', [TaskController::class, 'show']);
    // Route::put('projects/{projectId}/todoLists/{todoListId}/tasks/{taskId}', [TaskController::class, 'update']);
    // Route::delete('projects/{projectId}/todoLists/{todoListId}/tasks/{taskId}', [TaskController::class, 'destroy']);