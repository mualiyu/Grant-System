<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //register
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|unique:users,username',
            'phone' => 'required|unique:users,phone',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $request['password'] = Hash::make($request->password);

        $user = User::create($request->all());

        return response()->json([
            'status' => true,
            'data' => $user,
            'message' => 'Registration successfull.'
        ], 201);
    }
    
    //login
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'user' => $user,
                'token' => $user->createToken($request->device_name, ['Admin'])->plainTextToken
            ],
            'message' => 'Login successfull.'
        ]);
    }

       //recover
    public function recover(Request $request)
    {
    }

    //reset
    public function reset(Request $request)
    {
    }

    //verify
    public function verify(Request $request)
    {
    }

    //user
    public function user(Request $request)
    {
        if ($request->user()->tokenCan('Admin')) {
            return response()->json([
                'status' => true,
                'data' => [
                    'user' => $request->user(),
                ],
            ]);

        }else{
            // $request->user()->tokens()->delete();
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    //logout
    public function logout(Request $request)
    {
        if ($request->user()->tokenCan('Admin')) {
            $request->user()->tokens()->delete();
            return response()->json([
                'status' => true,
                'message' => "Logged out",
            ]);

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }
}
