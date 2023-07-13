<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationCv;
use App\Models\ApplicationDocument;
use App\Models\ApplicationFinancialDebtInfo;
use App\Models\ApplicationFinancialInfo;
use App\Models\ApplicationProfile;
use App\Models\ApplicationProject;
use App\Models\Lot;
use App\Models\Program;
use App\Models\ProgramDocument;
use App\Models\ProgramRequirement;
use App\Models\ProgramStage;
use App\Models\ProgramStatus;
use App\Models\SubLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// zip
use Illuminate\Support\Facades\File;
use ZipArchive;

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
            
            
            $stages = $prog['stages'];
            // $documents = $prog['uploads'];
            // $statuses = $prog['status'];
            // $milestones = $prog['milestones'];


            $program = Program::create([
                'name' => $prog['programName'],
                'description' => $prog['programDescription'],
            ]);

            // adding Lots and SubLots
            if (array_key_exists('lots', $prog)) {
                $lots = $prog['lots'];
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
            }

            // Adding Requirements
            if (array_key_exists('requirements', $prog)) {
                $requirements = $prog['requirements'];
                if (count($requirements)>0) {
                    foreach ($requirements as $key => $rq) {
                        $requirement = ProgramRequirement::create([
                            'name' => $rq['name'],
                            'type' => $rq['type'],
                            'program_id' => $program->id,
                        ]);
                    }
                }
            }

            // Adding Stages
            if (array_key_exists('stages', $prog)) {
                $stages = $prog['stages'];
                if (count($stages)>0) {
                    foreach ($stages as $ke => $st) {
                        $stages = ProgramStage::create([
                            'name' => $st['name'],
                            'start' => $st['startDate'],
                            'end' => $st['endDate'],
                            'description' => $st['description'],
                            'document' => $st['document'],
                            'program_id' => $program->id,
                            'isActive' => '1',
                        ]);
                    }
                }
            }


            // Adding Documents
            if (array_key_exists('uploads', $prog)) {
                $documents = $prog['uploads'];
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
            }

            // Adding Statuses
            if (array_key_exists('status', $prog)) {
                $statuses = $prog['status'];
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
        // if ($request->user()->tokenCan('Admin')) {

            // $programs = Program::with('lots')
            //                 ->with('sublots')
            //                 ->with('requirements')
            //                 ->with('documents')
            //                 ->with('stages')
            //                 ->with('statuses')->all();
            $programs = Program::all();

            foreach ($programs as $key => $p) {
                # code...
                $stages = ProgramStage::where(['program_id'=>$p->id, 'isActive'=>'1'])->get();
                
                $num_applicatnt = 0;
                $s = count($stages)>0 ? $stages[0]:'0';
                $p->activeStage = $s;
                $p->noApplicants = $num_applicatnt;
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'programs' => $programs,
                ],
            ]);
        // }else{
        //     return response()->json([
        //         'status' => false,
        //         'message' => trans('auth.failed')
        //     ], 404);
        // }
    }

    public function show(Request $request)
    {
        // if ($request->user()->tokenCan('Admin')) {

            $validator = Validator::make($request->all(), [
                'programId' => 'required',
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
                    'programs' => Program::where('id', '=', $request->programId)
                                    ->with('lots', 'sublots')
                                    // ->with('sublots')
                                    ->with('requirements')
                                    ->with('documents')
                                    ->with('stages')
                                    ->with('statuses')->get()[0],
                ],
            ]);
        // }else{
        //     return response()->json([
        //         'status' => false,
        //         'message' => trans('auth.failed')
        //     ], 404);
        // }
    }

    public function showObj(Request $request)
    {

            $validator = Validator::make($request->all(), [
                'programId' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $prog = Program::where('id', '=', $request->programId)->get()[0];
            $arr  = [
                "program" => [
                    "programName"=>$prog->name,
                    "programDescription"=>$prog->description,
                    "lots"=>array(),
                    "requirements"=>[],
                    "stages"=>[],
                    "uploads"=>[],
                    "status"=>[],
                ],
            ];

            foreach ($prog->lots as $key => $l) {
                $al = [
                    "name"=>$l->name,
                    "region"=>$l->region_id,
                    "category"=>$l->category_id,
                    "subLots"=>[],
                ];

                foreach ($l->sublots as $key => $sl) {
                    $as = [
                        "id"=>$sl->id,
                        "name"=>$sl->name,
                        "category"=>$sl->category_id
                    ];

                    array_push($al['subLots'],$as);
                }
                
                array_push($arr['program']['lots'],$al);
            }

            foreach ($prog->requirements as $key => $r) {
                $ar = [
                    "name"=>$r->name,
                    "type"=>$r->type,
                ];
                array_push($arr['program']['requirements'],$ar);
            }

            foreach ($prog->stages as $key => $s) {
                $ass = [
                    "name"=>$s->name,
                    "startDate"=>$s->start,
                    "endDate"=>$s->end,
                    "description"=>$s->description,
                    "document"=>$s->document,
                ];
                array_push($arr['program']['stages'],$ass);
            }

            foreach ($prog->documents as $key => $dd) {
                $auu = [
                    "name"=>$dd->name,
                    "file"=>$dd->url,
                ];
                array_push($arr['program']['uploads'],$auu);
            }   

            foreach ($prog->statuses as $key => $ss) {
                $aus = [
                    "name"=>$ss->name,
                    "isEditable"=>$ss->isInitial,
                    "isInitial"=>$ss->isEditable,
                    "color"=>$ss->color,
                ];
                array_push($arr['program']['status'],$aus);
            }  

            return response()->json([
                'status' => true,
                'data' => $arr,
            ]);

    }

    public function upload(Request $request)
    {
        if ($request->user()->tokenCan('Admin')) {

            $validator = Validator::make($request->all(), [
                'file' => 'required|max:9000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            
            if ($request->hasFile("file")) {
                $fileNameWExt = $request->file("file")->getClientOriginalName();
                $fileName = pathinfo($fileNameWExt, PATHINFO_FILENAME);
                $fileExt = $request->file("file")->getClientOriginalExtension();
                $fileNameToStore = $fileName."_".time().".".$fileExt;
                $request->file("file")->storeAs("public/programFiles", $fileNameToStore);

                $url = url('/storage/programFiles/'.$fileNameToStore);

                return response()->json([
                    'status' => true,
                    'message' => "File is successfully uploaded.",
                    'data' => [
                        'url' => $url,
                    ],
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Error! file upload invalid. Try again."
                ], 404);
            }

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }


    public function getApplications(Request $request)
    {
        if ($request->user()->tokenCan('Admin')) {
            $validator = Validator::make($request->all(), [
                'program_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $applications = Application::where(['program_id'=>$request->program_id, 'status'=>'1'])->with('applicant')->get();
            return response()->json([
                'status' => true,
                'data' => [
                    'applications' => $applications,
                    'count' => count($applications),
                ],
            ]);

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function getSingleApplication(Request $request) 
    {
        if ($request->user()->tokenCan('Admin')) {

            $validator = Validator::make($request->all(), [
                'program_id'=>'required',
                'applicant_id'=>'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $app = Application::where(['applicant_id'=> $request->applicant_id, 'program_id'=>$request->program_id])->with("sublots")->get();
            if (count($app)>0) {
                $app = $app[0];

                $app_profile = ApplicationProfile::where(["application_id"=>$app->id])->with('contact_persons')->with('share_holders')->get();
                $app_staff = ApplicationCv::where(["application_id"=>$app->id])->with('employers')->with('current_position')->get();

                $app_projects = ApplicationProject::where(["application_id"=>$app->id])->with('referees')->with('sub_contractors')->get();
                
                $app_fin = ApplicationFinancialInfo::where(["application_id"=>$app->id])->get();
                $app_fin_dept = ApplicationFinancialDebtInfo::where(["application_id"=>$app->id])->with('borrowers')->get();
                $fin = [
                    "financial_info" => $app_fin,
                    "financial_dept_info" => $app_fin_dept
                ];

                $sublots = DB::table('application_sub_lot')->where('application_id', $app->id)->get();
                if (count($sublots)>0) {
                    $subs = [];
                    
                    foreach ($sublots as $sl) {
                        
                        $s_sublot = SubLot::where('id', $sl->sub_lot_id)->get();
                        if (count($s_sublot)>0) {
                            $s_s = $s_sublot[0];
                            // return $s_s->name;
                            $arr = [
                                "sublot_id"=>$sl->sub_lot_id,
                                "choice"=>$sl->choice,
                                "sublot_name" => $s_s->name,
                                // "sublot_category" => $s_s->category->name,
                                "lot_name" => $s_s->lot->name,
                                "lot_region" => $s_s->lot->region->name,
                            ];
    
                            array_push($subs, $arr);
                        }
                    }
                }else{
                    $subs = [];
                }

                $app_docs = ApplicationDocument::where(["application_id"=>$app->id])->get();

                $app['application_profile'] = count($app_profile)>0 ? $app_profile: [];
                $app['application_staff'] = $app_staff;
                $app['application_projects'] = $app_projects;

                $app['application_financials'] = $fin;
                $app['application_documents'] = $app_docs;

                $app['application_sublots'] = $subs;

                return response()->json([
                    'status' => true,
                    'message' => "Successful.",
                    'data' => [
                        "application"=>$app
                    ]
                ]);                

            }else{
                return response()->json([
                    'status' => false,
                    'message' => "No Application found"
                ], 422);
            }
            
        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    // public function downloadApplicationDocuments(Request $request)
    // {
    //     if ($request->user()->tokenCan('Admin')) {

    //         $zip = new ZipArchive;

    //         $fileName = 'myNewFile.zip';

    //         if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE) {
    //             $files = File::files(public_path('myFiles'));

    //             foreach ($files as $key => $value) {
    //                 $relativeNameInZipFile = basename($value);
    //                 $zip->addFile($value, $relativeNameInZipFile);
    //             }

    //             $zip->close();
    //         }

    //         return response()->download(public_path($fileName));

    //         // return response()->json([
    //         //     'status' => true,
    //         //     'data' => [
    //         //         'applications' => '',
    //         //     ],
    //         // ]);

    //     } else {
    //         return response()->json([
    //             'status' => false,
    //             'message' => trans('auth.failed')
    //         ], 404);
    //     }
    // }
}
