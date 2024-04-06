<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        if ($validated->fails()) {
            return response()->json($validated->messages(), 400);
        } else {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ];
            DB::beginTransaction();
            try {
                $user = User::create($data);
                $token = $user->createToken('auth_token')->accessToken;
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $user = null;
            }
            if ($user != null) {
                return response()->json([
                    'token' => $token,
                    'user' => $user,
                    'message' => 'User created successfully',
                    'status' => 1
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Internal Server Error',
                ], 500);
            }
        }
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($validated)) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->accessToken;

            return response()->json([
                'token' => $token,
                'user' => $user,
                'message' => 'User logged in successfully',
                'status' => 1
            ], 200);
        } else {
            return response()->json([
                'message' => 'Email or password not matched',
                'status' => 0
            ], 401);
        }
    }

    public function getUser($id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            $reponse = [
                'message' => 'User not found',
                'status' => 0
            ];
        } else {
            $reponse = [
                'message' => 'User found',
                'stauts' => 1,
                'data' => $user
            ];
        }

        return response()->json($reponse, 200);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'User logged out successfully',
            'status' => 1
        ], 200);
    }
}
