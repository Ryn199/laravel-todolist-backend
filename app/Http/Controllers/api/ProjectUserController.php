<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProjectUserController extends Controller
{


    public function index(Request $request)
    {
        try {
            // Ambil ID user yang sedang login (diasumsikan sudah menggunakan auth middleware)
            $userId = $request->user()->id;

            // Filter project yang hanya ter-assign ke user tersebut
            $projects = Project::whereHas('users', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })->with('users:id,name')->get();

            // Pastikan URL gambar konsisten
            $projects = $projects->map(function ($project) {
                if ($project->image && !str_contains($project->image, 'http')) {
                    $project->image = url('storage/' . $project->image);
                }
                return $project;
            });

            return response()->json($projects, 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data project.'], 500);
        }
    }




    public function assignUserToProject($projectId, $userId)
    {
        try {
            $project = Project::find($projectId);
            $user = User::find($userId);

            if (!$project || !$user) {
                return response()->json(['message' => 'Project atau User tidak ditemukan.'], 404);
            }

            // Debugging: Pastikan query yang dijalankan tepat
            $exists = $project->users()->where('user_id', $userId)->exists();
            if ($exists) {
                return response()->json(['message' => 'User sudah terdaftar di project.'], 409);
            }

            // Jika tidak ada, tambah user ke project
            $project->users()->attach($userId);

            return response()->json(['message' => 'User berhasil ditambahkan ke project.'], 200);
        } catch (Exception $e) {
            // Debug error
            return response()->json([
                'message' => 'Terjadi kesalahan saat menambahkan user ke project.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function unassignUserFromProject($projectId, $userId)
    {
        try {
            $project = Project::find($projectId);
            $user = User::find($userId);

            if (!$project || !$user) {
                return response()->json(['message' => 'Project atau User tidak ditemukan.'], 404);
            }

            $project->users()->detach($userId);

            return response()->json(['message' => 'User berhasil dihapus dari project.'], 200);
        } catch (Exception $e) {
            // Debug error
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus user dari project.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    
}
