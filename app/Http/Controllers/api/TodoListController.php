<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\TodoList;
use App\Models\Project;
use Illuminate\Http\Request;
use Exception;

class TodoListController extends Controller
{
    // Menampilkan semua todo list dari project
    public function index($projectId)
    {
        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json(['message' => 'Project tidak ditemukan.'], 404);
            }

            $todoLists = TodoList::where('project_id', $projectId)->with('tasks')->get();
            return response()->json($todoLists, 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data Todo List.'], 500);
        }
    }

    // Menambahkan todo list
    public function store(Request $request, $projectId)
    {
        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json(['message' => 'Project tidak ditemukan.'], 404);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $todoList = TodoList::create([
                'project_id' => $projectId,
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return response()->json($todoList, 201); // 201: Created
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal menambahkan Todo List.'], 500);
        }
    }

    // Menampilkan todo list berdasarkan ID
    public function show($projectId, $todoListId)
    {
        try {
            $projectExists = Project::find($projectId);
            if (!$projectExists) {
                return response()->json(['message' => 'Project tidak ditemukan.'], 404);
            }
    
            $todoList = TodoList::where('project_id', $projectId)
                ->where('id', $todoListId)
                ->with('tasks')
                ->first();
    
            if (!$todoList) {
                return response()->json(['message' => 'Todo List tidak ditemukan dalam Project ini.'], 404);
            }
    
            return response()->json($todoList, 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data Todo List.'], 500);
        }
    }
    
    // Mengupdate todo list
    public function update(Request $request, $projectId, $todoListId)
    {
        try {
            $todoList = TodoList::where('project_id', $projectId)
                ->where('id', $todoListId)
                ->first();

            if (!$todoList) {
                return response()->json(['message' => 'Todo List tidak ditemukan.'], 404);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $todoList->update($request->all());
            return response()->json($todoList, 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui Todo List.'], 500);
        }
    }

    // Menghapus todo list
    public function destroy($projectId, $todoListId)
    {
        try {
            $todoList = TodoList::where('project_id', $projectId)
                ->where('id', $todoListId)
                ->first();

            if (!$todoList) {
                return response()->json(['message' => 'Todo List tidak ditemukan.'], 404);
            }

            $todoList->delete();
            return response()->json(['message' => 'Todo List berhasil dihapus.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal menghapus Todo List.'], 500);
        }
    }
}
