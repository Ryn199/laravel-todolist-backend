<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;

class TaskController extends Controller
{
    // Menampilkan semua task dari todo list
    public function index($projectId, $todoListId)
    {
        try {
            $todoList = TodoList::where('project_id', $projectId)
                ->where('id', $todoListId)
                ->first();

            if (!$todoList) {
                return response()->json(['message' => 'Todo List tidak ditemukan.'], 404);
            }

            $tasks = $todoList->tasks;
            return response()->json($tasks, 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data.'], 500);
        }
    }

    // Menambahkan task baru
    public function store(Request $request, $projectId, $todoListId)
    {
        
        try {
            $todoList = TodoList::where('project_id', $projectId)
                ->where('id', $todoListId)
                ->first();

            if (!$todoList) {
                return response()->json(['message' => 'Todo List tidak ditemukan.'], 404);
            }

            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:completed,incomplete,cancelled,overdue,in_progress',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
            ]);

            $task = Task::create([
                'todo_list_id' => $todoListId,
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            return response()->json($task, 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal menambahkan task.'], 500);
        }
    }

    // Mengubah task
    public function update(Request $request, $projectId, $todoListId, $taskId)
    {
        try {
            // Cari TodoList berdasarkan project_id dan todoListId
            $todoList = TodoList::where('project_id', $projectId)
                ->where('id', $todoListId)
                ->first();

            if (!$todoList) {
                return response()->json(['message' => 'Todo List tidak ditemukan.'], 404);
            }

            // Cari Task berdasarkan taskId
            $task = Task::find($taskId);

            if (!$task) {
                return response()->json(['message' => 'Task tidak ditemukan.'], 404);
            }

            // Validasi input request
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:completed,incomplete,cancelled,overdue,in_progress',
                'start_time' => 'nullable|date',
                'end_time' => 'required|date|after:start_time',
            ]);

            // Update task
            $task->update($validated);
            return response()->json($task, 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui task.'], 500);
        }
    }

    // Menampilkan detail task
    public function show($projectId, $todoListId, $taskId)
    {
        try {
            // Cari TodoList berdasarkan project_id dan todoListId
            $todoList = TodoList::where('project_id', $projectId)
                ->where('id', $todoListId)
                ->first();

            if (!$todoList) {
                return response()->json(['message' => 'Todo List tidak ditemukan.'], 404);
            }

            // Cari Task berdasarkan taskId
            $task = Task::find($taskId);

            if (!$task) {
                return response()->json(['message' => 'Task tidak ditemukan.'], 404);
            }

            return response()->json($task, 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data.'], 500);
        }
    }

    // Menghapus task
    public function destroy($projectId, $todoListId, $taskId)
    {
        try {
            // Cari TodoList berdasarkan project_id dan todoListId
            $todoList = TodoList::where('project_id', $projectId)
                ->where('id', $todoListId)
                ->first();

            if (!$todoList) {
                return response()->json(['message' => 'Todo List tidak ditemukan.'], 404);
            }

            // Cari Task berdasarkan taskId
            $task = Task::find($taskId);

            if (!$task) {
                return response()->json(['message' => 'Task tidak ditemukan.'], 404);
            }

            // Hapus task
            $task->delete();
            return response()->json(['message' => 'Task berhasil dihapus.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal menghapus task.'], 500);
        }
    }

}
