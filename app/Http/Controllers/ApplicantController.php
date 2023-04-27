<?php

namespace App\Http\Controllers;

use App\Mail\AcceptApplicantMail;
use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ApplicantController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:applicants,email',
            'username' => 'required|unique:applicants,username',
            'phone' => 'required|unique:applicants,phone',
            'person_incharge' => 'required',
            'rc_number'=>'required',
            'address'=>'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // $request['password'] = Hash::make($request->password);

        $pass = mt_rand(10000000,99999999);

        $password = Hash::make($pass);

        $request['password'] = $password;

        $user = Applicant::create($request->all());

        $mailData = [
            'title' => 'Your registration is successful.',
            'body' => 'Use Username: '.$user->username.' & Password: '.$pass,
        ];
        // return "Your username is: ".$request->username." & password is: ".$pass;
        Mail::to($user->email)->send(new AcceptApplicantMail($mailData));

        return response()->json([
            'status' => true,
            'data' => $user,
            'message' => 'Registration successfull.'
        ], 201);
    }

    //verify
    public function verify(Request $request)
    {
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

        $user = Applicant::where('username', $request->username)->first();

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
                'token' => $user->createToken($request->device_name, ['Applicant'])->plainTextToken
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

    //user
    public function user(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {
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
        if ($request->user()->tokenCan('Applicant')) {
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
