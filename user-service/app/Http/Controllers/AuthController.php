<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            Log::info('REGISTER DIPANGGIL', [
                'email' => $request->email
            ]);

            $request->validate([
                'name'     => 'required|string',
                'email'    => 'required|email|unique:users',
                'password' => 'required|min:6'
            ]);

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Register berhasil',
                'user'    => $user,
                'token'   => $token
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Let validation exceptions bubble so they render appropriately
            throw $e;
        } catch (\Exception $e) {
            $corr = $request->header('X-Correlation-ID') ?? (app()->bound('correlation_id') ? app('correlation_id') : Str::uuid()->toString());
            Log::error('REGISTER Error', ['error' => $e->getMessage(), 'correlation_id' => $corr]);
            return response()->json(['error' => 'Internal server error', 'correlation_id' => $corr], 500)
                ->header('X-Correlation-ID', $corr);
        }
    }

    public function login(Request $request)
    {
        try {
            Log::info('LOGIN DIPANGGIL', [
                'email' => $request->email
            ]);

            $request->validate([
                'email'    => 'required|email',
                'password' => 'required'
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'message' => 'Email atau password salah'
                ], 401);
            }

            $user  = User::where('email', $request->email)->first();
            $token = $user->createToken('auth_token')->plainTextToken;

            // Log successful login including correlation id for traceability (generate fallback if missing)
            $corr = $request->header('X-Correlation-ID') ?? (app()->bound('correlation_id') ? app('correlation_id') : \Illuminate\Support\Str::uuid()->toString());
            \Illuminate\Support\Facades\Log::info('USER LOGGED IN', [
                'user_id' => $user->id,
                'email' => $user->email,
                'correlation_id' => $corr,
            ]);

            return response()->json([
                'message' => 'Login berhasil',
                'user'    => $user,
                'token'   => $token
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $corr = $request->header('X-Correlation-ID') ?? (app()->bound('correlation_id') ? app('correlation_id') : Str::uuid()->toString());
            Log::error('LOGIN Error', ['error' => $e->getMessage(), 'correlation_id' => $corr]);
            return response()->json(['error' => 'Internal server error', 'correlation_id' => $corr], 500)
                ->header('X-Correlation-ID', $corr);
        }
    }
}