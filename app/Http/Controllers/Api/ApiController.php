<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($flag)
    {
        $query = User::select('name', 'email');

        if ($flag == 1) {
            $query->where('status', 1);
        } else {
            return response()->json([
                'message' => 'Invalid parameter passed',
                'status' => 0
            ], 400);
        }
        $users = $query->get();
        if (count($users) > 0) {
            $reponse = [
                'message' => count($users) . ' Users Found',
                'status' => 1,
                'data' => $users
            ];
        } else {
            $reponse = [
                'message' => count($users) . ' Users Found',
                'status' => 0,
            ];
        }

        return response()->json($reponse, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $user = null;
            }

            if ($user != null) {
                return response()->json([
                    'message' => 'User Registered Successfully',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Internal Server Error',
                ], 500);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            $response = [
                'message' => 'User does not exists',
                'status' => 0
            ];
            $resCode = 404;
        } else {
            DB::beginTransaction();
            try {
                $user->name = $request['name'];
                $user->email = $request['email'];
                $user->contact = $request['contact'];
                $user->pincode = $request['pincode'];
                $user->address = $request['address'];
                $user->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $user = '';
            }
            if (is_null($user)) {
                $response = [
                    'message' => 'Internal server error',
                    'error' => $e->getMessage(),
                    'status' => 0
                ];
                $resCode = 500;
            } else {
                $response = [
                    'message' => 'User updated successfully',
                    'status' => 1
                ];
                $resCode = 200;
            }
        }

        return response()->json($response, $resCode);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            $reponse = [
                'message' => "User does't exits",
                'status' => 0
            ];
            $resCode = 404;
        } else {
            DB::beginTransaction();
            try {
                $user->delete();
                DB::commit();
                $reponse = [
                    'message' => 'User delete successfully',
                    'status' => 1
                ];
                $resCode = 200;
            } catch (\Exception $e) {
                DB::rollBack();
                $reponse = [
                    'message' => 'Internal server error',
                    'status' => 0
                ];
                $resCode = 500;
            }
        }

        return response()->json($reponse, $resCode);
    }

    public function changePassword(Request $request, $id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            $reponse = [
                'message' => "User does't exits",
                'status' => 0
            ];
            $resCode = 404;
        } else {
            if (Hash::check($request['old_password'], $user->password)) {
                if ($request['new_password'] == $request['confirm_password']) {
                    DB::beginTransaction();
                    try {
                        $user->password = Hash::make($request['new_password']);
                        $user->save();
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $user = '';
                    }
                    if (is_null($user)) {
                        return response()->json([
                            'message' => 'Internal server error',
                            'error' => $e->getMessage(),
                            'status' => 0
                        ], 500);
                    } else {
                        return response()->json([
                            'message' => 'Password changed updated successfully',
                            'status' => 1
                        ], 200);
                    }
                } else {
                    $reponse = [
                        'message' => "New password and confirm password does not match",
                        'status' => 0
                    ];
                    $resCode = 400;
                }
            } else {
                $reponse = [
                    'message' => "Old password does not match",
                    'status' => 0
                ];
                $resCode = 400;
            }
        }
        return response()->json($reponse, $resCode);
    }
}
