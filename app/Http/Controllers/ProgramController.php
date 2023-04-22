<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProgramController extends Controller
{
    
    public function create(Request $request)
    {
        if ($request->user()->tokenCan('Admin')) {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'description' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'user' => $request->user(),
                ],
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function showAll(Request $request)
    {
        if ($request->user()->tokenCan('Admin')) {

            $programs = Program::all()->with('lots');

            return response()->json([
                'status' => true,
                'data' => [
                    'programs' => $programs,
                ],
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }
}
