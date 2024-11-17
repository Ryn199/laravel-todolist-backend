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
            $project = Project::get();
            return response()->json($project, 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data project.'], 500);
        }
    }

    // Menambahkan project baru
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $randomImageUrl = 'https://random-image-pepebigotes.vercel.app/api/random-image';
            $imageContents = file_get_contents($randomImageUrl);

            if ($imageContents === false) {
                return response()->json(['message' => 'Gagal mengambil gambar dari API.'], 500);
            }

            $imageName = 'project_' . uniqid() . '.jpg';
            $imagePath = 'projects/' . $imageName;
            Storage::put($imagePath, $imageContents);

            // Buat project baru
            $project = Project::create([
                'name' => $request->name,
                'description' => $request->description,
                'image' => Storage::url($imagePath),
            ]);

            return response()->json($project, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Debugging error
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
