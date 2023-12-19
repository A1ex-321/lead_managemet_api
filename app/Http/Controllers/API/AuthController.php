<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Hash;

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
            ]);
            // Create a new user
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);
            return response()->json(['message' => 'User registered successfully',], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed'], 500);
        }
    }
    public function loginUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);
            if (!$request->password && !$request->email) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email and password required',

                ], 401);
            } else if (!$request->email) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email required',
                ], 401);
            } else if (!$request->password) {
                return response()->json([
                    'status' => false,
                    'message' => 'password required',
                ], 401);
            }


            $credentials = $request->only(['email', 'password']);

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email and password do not match our records.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'User logged in successfully.',
                'user_details' => $user,
                'token' => $user->createToken('API TOKEN', ['expires_in' => 60 * 60])->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
