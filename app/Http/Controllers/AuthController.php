<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Register User
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }

    // Fungsi login user
    public function login(Request $request)
    {
        // Validasi input email dan password
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Jika user tidak ditemukan
        if (!$user) {
            return response()->json([
                'message' => 'Email belum terdaftar.',
            ], 404);
        }

        // Jika password salah
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Password salah.',
            ], 401);
        }


        $response = [
            'message' => 'Login berhasil.',
            'username' => $user->name,
            'email' => $user->email,
            'token' => $user->createToken('auth_token')->plainTextToken
        ];

        return response()->json([
            'data' => $response
        ]);
    }
    // Logout User
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'User logged out successfully',
        ]);
    }
}
