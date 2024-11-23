<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    // Menampilkan semua project
    public function index()
    {
        try {
            // Ambil semua proyek beserta pengguna yang ter-assign
            $projects = Project::with(['users' => function ($query) {
                $query->select('users.id', 'users.email'); // Ambil hanya ID dan email user
            }])->get();
    
            // Format data agar hanya menampilkan email pengguna
            $projectsWithUsers = $projects->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'assigned_users' => $project->users->pluck('email'), // Ambil hanya email pengguna
                ];
            });
    
            return response()->json($projectsWithUsers, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data project.'], 500);
        }
    }
    
    

    // Menambahkan project
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);
    
            // Ambil user yang sedang login
            $user = $request->user();
    
            if (!$user) {
                return response()->json(['message' => 'User tidak ditemukan.'], 404);
            }
    
            // Ambil gambar random dari API
            $randomImageUrl = 'https://picsum.photos/200/300';
            $imageContents = file_get_contents($randomImageUrl);
    
            if ($imageContents === false) {
                return response()->json(['message' => 'Gagal mengambil gambar dari API.'], 500);
            }
    
            // Simpan gambar ke storage
            $imageName = 'project_' . uniqid() . '.jpg';
            $imagePath = 'projects/' . $imageName;
            Storage::put($imagePath, $imageContents);
    
            // Buat project baru
            $project = Project::create([
                'name' => $request->name,
                'description' => $request->description,
                'image' => Storage::url($imagePath),
            ]);
    
            // Assign user ke project
            $project->users()->attach($user->id);
    
            return response()->json($project, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menambahkan project.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    // Mengubah project
    public function update(Request $request, $id)
    {
        try {
            $project = Project::find($id);

            if (!$project) {
                return response()->json(['message' => 'Project tidak ditemukan.'], 404);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $project->update($request->all());
            return response()->json($project, 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui project.'], 500);
        }
    }

    // Menampilkan project berdasarkan ID
    public function show($projectId)
    {
        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json(['message' => 'Project tidak ditemukan.'], 404);
            }

            return response()->json($project, 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data project.'], 500);
        }
    }


    // Menghapus project
    public function destroy($id)
    {
        try {
            $project = Project::find($id);

            if (!$project) {
                return response()->json(['message' => 'Project tidak ditemukan.'], 404);
            }

            $project->delete();
            return response()->json(['message' => 'Project berhasil dihapus.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal menghapus project.'], 500);
        }
    }

    public function updateImage(Request $request, $id)
    {
        try {
            $project = Project::find($id);

            if (!$project) {
                return response()->json(['message' => 'Project tidak ditemukan.'], 404);
            }

            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = 'project_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = 'projects/' . $imageName;
                Storage::put($imagePath, file_get_contents($image));
                $project->image = Storage::url($imagePath);
                $project->save();
            }

            return response()->json($project, 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui gambar project.'], 500);
        }
    }
}
