<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class TaskUserController extends Controller
{

    public function index()
    {
        try {
            $task = Task::with('users')->get();
            return response()->json($task, 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data project.'], 500);
        }
    }
    // Assign user ke task
    public function assignUserToTask($projectId, $taskId, $userId)
    {
        try {
            // Validasi parameter sebagai integer
            $projectId = (int) $projectId;
            $taskId = (int) $taskId;
            $userId = (int) $userId;

            $project = Project::find($projectId);
            $task = Task::find($taskId);
            $user = User::find($userId);

            if (!$project || !$task || !$user) {
                return response()->json(['message' => 'Project, Task, atau User tidak ditemukan.'], 404);
            }

            if (!$task->todoList) {
                return response()->json(['message' => 'Task tidak memiliki todo list.'], 403);
            }

            if ($task->todoList->project_id != $projectId) {
                return response()->json([
                    'message' => 'Task tidak berada di dalam project ini.',
                    'task_project_id' => $task->todoList->project_id,
                    'requested_project_id' => $projectId,
                ], 403);
            }

            if (!$project->users->contains($userId)) {
                return response()->json(['message' => 'User tidak berada di project ini.'], 403);
            }

            // Assign user ke task
            $task->users()->syncWithoutDetaching($userId);

            return response()->json(['message' => 'User berhasil di-assign ke task.'], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat meng-assign user ke task.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // Unassign user dari task
    public function unassignUserFromTask($taskId, $userId)
    {
        try {
            $Task = Task::find($taskId);
            $user = User::find($userId);

            if (!$Task || !$user) {
                return response()->json(['message' => 'Task atau User tidak ditemukan.'], 404);
            }

            $Task->users()->detach($userId);

            return response()->json(['message' => 'User berhasil dihapus dari task.'], 200);
        } catch (Exception $e) {
            // Debug error
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus user dari Task.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
