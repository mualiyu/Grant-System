<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\Program;
use App\Models\ProgramDocument;
use App\Models\ProgramRequirement;
use App\Models\ProgramStage;
use App\Models\ProgramStatus;
use App\Models\SubLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProgramController extends Controller
{
    
    public function create(Request $request)
    {
        if ($request->user()->tokenCan('Admin')) {

            $validator = Validator::make($request->all(), [
                'program' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $prog = $request->program;
            $lots = $prog['lots'];
            $requirements = $prog['requirements'];
            $stages = $prog['stages'];
            $documents = $prog['uploads'];
            $statuses = $prog['status'];
            // $milestones = $prog['milestones'];


            $program = Program::create([
                'name' => $prog['programName'],
                'description' => $prog['programDescription'],
            ]);

            // adding Lots and SubLots
            if (count($lots)>0) {
                foreach ($lots as $key => $l) {
                    $lot = Lot::create([
                        'name' => $l['name'],
                        'category_id' => $l['category'],
                        'region_id' => $l['region'],
                        'program_id' => $program->id,
                    ]);
    
                    foreach ($l['subLots'] as $k => $sl) {
                        SubLot::create([
                            'name' => $sl['name'],
                            'category_id' => $sl['category'],
                            'lot_id' => $lot->id,
                            'program_id' => $program->id,
                        ]);
    
                    }
                }
            }

            // Adding Requirements
            if (count($requirements)>0) {
                foreach ($requirements as $key => $rq) {
                    $requirement = ProgramRequirement::create([
                        'name' => $rq['name'],
                        'type' => $rq['type'],
                        'program_id' => $program->id,
                    ]);
                }
            }

            // Adding Stages
            if (count($stages)>0) {
                foreach ($stages as $ke => $st) {
                    $requirement = ProgramStage::create([
                        'name' => $st['name'],
                        'start' => $st['startDate'],
                        'end' => $st['endDate'],
                        'description' => $st['description'],
                        'program_id' => $program->id,
                    ]);
                }
            }


            // Adding Documents
            if (count($documents)>0) {
                foreach ($documents as $doc) {
                    $d = ProgramDocument::create([
                        'name' => $doc['name'],
                        'url' => $doc['file'],
                        'type'=>"pdf",
                        'program_id' => $program->id,
                    ]);
                }
            }

            // Adding Statuses
            if (count($statuses)>0) {
                foreach ($statuses as $ks => $sta) {
                    $d = ProgramStatus::create([
                        'name' => $sta['name'],
                        'isInitial' => $sta['isInitial'],
                        'isEditable' => $sta['isEditable'],
                        'color' => $sta['color'],
                        'program_id' => $program->id,
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => "Successfully created Program.....",
                'data' => [
                    'Program' => Program::where('id', '=', $program->id)
                                    ->with('lots', 'sublots')
                                    // ->with('sublots')
                                    ->with('requirements')
                                    ->with('documents')
                                    ->with('stages')
                                    ->with('statuses')->get()[0],
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

            $programs = Program::all()
                            ->with('lots', 'sublots')
                            // ->with('sublots')
                            ->with('requirements')
                            ->with('documents')
                            ->with('stages')
                            ->with('statuses');

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
