<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // =====================
    // REGISTER
    // =====================
    public function register(Request $request)
    {
        // LOG: register dipanggil
        Log::info('REGISTER DIPANGGIL', [
            'email' => $request->email
        ]);

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // LOG: register berhasil
        Log::info('REGISTER BERHASIL', [
            'user_id' => $user->id,
            'email'   => $user->email
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        Log::info('TOKEN REGISTER DIBUAT', [
    'user_id' => $user->id,
    'email'   => $user->email,
    'token'   => $token,
]);

        return response()->json([
            'message' => 'Register berhasil',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    // =====================
    // LOGIN
    // =====================
    public function login(Request $request)
    {
        // LOG: login dipanggil
        Log::info('LOGIN DIPANGGIL', [
            'email' => $request->email
        ]);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            // LOG: login gagal
            Log::warning('LOGIN GAGAL', [
                'email' => $request->email
            ]);

            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();

        // LOG: login berhasil
        Log::info('LOGIN BERHASIL', [
            'user_id' => $user->id,
            'email'   => $user->email
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        Log::info('TOKEN LOGIN DIBUAT', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'token'   => $token,
        ]);

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token
        ]);
    }
}
