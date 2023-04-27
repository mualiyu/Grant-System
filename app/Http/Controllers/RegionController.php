<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function showAll(Request $request)
    {
        // if ($request->user()) { // if ($request->user()->tokenCan('Admin')) {

            $regions = Region::all();

            return response()->json([
                'status' => true,
                'data' => [
                    'regions' => $regions,
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
