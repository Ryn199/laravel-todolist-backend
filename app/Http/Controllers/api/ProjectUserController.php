<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;


class ProjectUserController extends Controller
{


    public function index(){
        try {
            $project = Project::with('users')->get();
            return response()->json($project, 200);
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

            if ($project->users()->where('user_id', $userId)->exists()) {
                return response()->json(['message' => 'User sudah terdaftar di project.'], 409);
            }

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
