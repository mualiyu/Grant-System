<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function create(Request $request)
    {
        if ($request->user()->tokenCan('Admin')) {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            Category::create($request->all());

            $cat = Category::all();

            return response()->json([
                'status' => true,
                'data' => [
                    'categories' => $cat,
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
        // if ($request->user()->tokenCan('Admin')) {

            $categories = Category::all();

            return response()->json([
                'status' => true,
                'data' => [
                    'categories' => $categories,
                ],
            ]);
        // }else{
        //     return response()->json([
        //         'status' => false,
        //         'message' => trans('auth.failed')
        //     ], 404);
        // }
    }
}
