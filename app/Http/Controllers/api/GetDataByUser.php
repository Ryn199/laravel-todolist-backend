<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class GetDataByUser extends Controller
{
    // Mendapatkan proyek yang terassign ke user
    public function getProjectsByUser($iduser)
    {
        try {
            // Validasi user yang login
            $user = User::findOrFail($iduser);

            // Ambil proyek yang terhubung dengan user
            $projects = $user->projects()->with('users:id,email')->get();

            $formattedProjects = $projects->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'image' => $project->image,
                    'assigned_users' => $project->users->pluck('email'), // Hanya ambil email user
                ];
            });

            return response()->json($formattedProjects, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data project.'], 500);
        }
    }

    // Mendapatkan todo lists berdasarkan project
    public function getTodoListsByProject($projectId)
    {
        try {
            // Ambil user yang sedang login
            $authUser = auth('sanctum')->user();

            // Validasi apakah user memiliki akses ke project ini
            $project = Project::where('id', $projectId)
                ->whereHas('users', function ($query) use ($authUser) {
                    $query->where('users.id', $authUser->id); // Validasi user
                })->firstOrFail();

            // Ambil todo list beserta tasks-nya yang terkait dengan project ini
            $todoLists = $project->todoLists()
                ->with(['tasks' => function ($query) {
                    $query->select('id', 'todo_list_id', 'title', 'description', 'status', 'start_time', 'end_time', 'created_at', 'updated_at');
                }])
                ->get(['id', 'name', 'description', 'project_id', 'created_at', 'updated_at']);

            return response()->json($todoLists, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses ke project ini atau project tidak ditemukan.',
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data todo list.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }





    // Mendapatkan task berdasarkan todo list
    public function getTasksByTodoList($idtodolist)
    {
        try {
            // Ambil user yang sedang login
            $authUser = auth('sanctum')->user();

            // Validasi akses user ke todo list
            $todoList = TodoList::where('id', $idtodolist)
                ->whereHas('project.users', function ($query) use ($authUser) {
                    $query->where('id', $authUser->id);
                })
                ->firstOrFail();

            // Ambil semua task terkait dengan todo list
            $tasks = $todoList->tasks()->get(['id', 'name', 'status', 'todo_list_id']);

            return response()->json($tasks, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke todo list ini.', 'error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data tasks.', 'error' => $e->getMessage()], 500);
        }
    }

    public function getTasksByUser($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        $userProjects = $user->projects;
    
        // Ambil semua task yang terkait dengan proyek-proyek tersebut
        $tasks = Task::whereHas('todoList', function ($query) use ($userProjects) {
            $query->whereIn('project_id', $userProjects->pluck('id'));
        })->get();
    
        return response()->json($tasks);
    }
    

}
