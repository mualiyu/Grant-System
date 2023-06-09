<?php

namespace App\Http\Controllers;

use App\Mail\MessageNotificationMail;
use App\Models\Application;
use App\Models\ApplicationCurrentPosition;
use App\Models\ApplicationCv;
use App\Models\ApplicationDocument;
use App\Models\ApplicationEducation;
use App\Models\ApplicationEmployer;
use App\Models\ApplicationFinancialDebtInfo;
use App\Models\ApplicationFinancialDebtInfoBorrower;
use App\Models\ApplicationFinancialInfo;
use App\Models\ApplicationMembership;
use App\Models\ApplicationProfile;
use App\Models\ApplicationProject;
use App\Models\ApplicationProjectReferee;
use App\Models\ApplicationProjectSubContractor;
use App\Models\ApplicationSubLot;
use App\Models\ApplicationTraining;
use App\Models\ContactPerson;
use App\Models\ShareHolder;
use App\Models\SubLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    public function createInitial(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

            $validator = Validator::make($request->all(), [
                'program_id' => 'required',
                'sublots'=> 'required',
                'update' => 'nullable',
                'application_id' => 'nullable',
                // 'choice'=>'nullable',
                
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            if ($request->update == "1") {
                $application = Application::where("id", $request->application_id)->get();
                $application = $application[0];
                
                DB::table("application_sub_lot")->where("application_id", $application->id)->delete();
            }else{
                $application = Application::create([
                    'applicant_id'=>$request->user()->id,
                    'program_id'=>$request->program_id,
                ]);
            }

            foreach ($request->sublots as $key => $sub) {
                DB::table('application_sub_lot')->insert([
                    'application_id'=>$application->id,
                    'sub_lot_id'=>$sub['id'],
                    'choice'=>$sub['choice'],
                ]);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'application' => Application::where('id', $application->id)->get()[0],
                ],
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function createProfile(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

            $validator = Validator::make($request->all(), [
                'update'=>'nullable',
                'application_id'=>'required',
                'applicant_name' => 'required',
                'date_of_incorporation'=> 'required',
                // 'brief_description'=> 'nullable',
                // 'website'=> 'nullable',
                'share_holders'=> 'nullable',
                'ultimate_owner'=> 'nullable',
                'contact_person'=> 'nullable',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            if ($request->update == "1") {
                $applicationP = ApplicationProfile::where("application_id", $request->application_id)->get();
                $applicationP = $applicationP[0];

                ApplicationProfile::where("id", $applicationP->id)->update([
                    'name' => $request->applicant_name,
                    'registration_date' => $request->date_of_incorporation,
                    // 'description' => $request->brief_description,
                    // 'website' => $request->website,
                    'cac_number'=>$request->user()->rc_number,
                    'address'=>$request->user()->address,
                    'owner'=>$request->ultimate_owner,
                ]);

                ContactPerson::where("app_prof_id", $applicationP->id)->delete();
                ShareHolder::where("app_prof_id", $applicationP->id)->delete();
            }else{
                $applicationP = ApplicationProfile::create([
                    'applicant_id' => $request->user()->id,
                    'application_id' => $request->application_id,
                    'name' => $request->applicant_name,
                    'registration_date' => $request->date_of_incorporation,
                    // 'description' => $request->brief_description,
                    // 'website' => $request->website,
                    'cac_number'=>$request->user()->rc_number,
                    'address'=>$request->user()->address,
                    'owner'=>$request->ultimate_owner,
                ]);
            }

            if (count($request->contact_person) > 0) {
                
                foreach ($request->contact_person as $key => $cp) {
                    $contact = ContactPerson::create([
                        "app_prof_id"=>$applicationP->id,
                        "name"=>$cp['name'],
                        "phone"=>$cp['phone'],
                        "email"=>$cp['email'],
                        "address"=>$cp['address'],
                        "designation"=>$cp["designation"]
                    ]);
                }
            }

            if (count($request->share_holders) > 0) {
                # code...
                foreach ($request->share_holders as $key => $sh) {
                    $contact = ShareHolder::create([
                        "app_prof_id"=>$applicationP->id,
                        "name"=>$sh['name'],
                        "phone"=>$sh['phone'],
                    ]);
                }
            }

            $appP = ApplicationProfile::where('id', $applicationP->id)->with('share_holders')->with('contact_persons')->get();

            return response()->json([
                'status' => true,
                'data' => [
                    'application_profile' => $appP,
                ],
            ]);

            
        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }


    public function createProfileUpdate(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

            $validator = Validator::make($request->all(), [
                'application_id'=>'required',
                'application_profile_id'=>'required',
                'brief_description'=> 'nullable',
                'website'=> 'nullable',
                'evidence_of_equipment_ownership'=> 'nullable',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $applicationP = ApplicationProfile::where('id', $request->application_profile_id)->update([
                'description' => $request->brief_description,
                'website' => $request->website,
                'evidence_of_equipment_ownership' => $request->evidence_of_equipment_ownership,
            ]);

            $appP = ApplicationProfile::where('id', $request->application_profile_id)->with('share_holders')->with('contact_persons')->get();

            return response()->json([
                'status' => true,
                'data' => [
                    'application_profile' => $appP,
                ],
            ]);

            
        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }


    public function createStaff(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

            $validator = Validator::make($request->all(), [
                'update'=>'nullable',
                'application_id'=>'required',
                'staff' => 'nullable',
                // 'choice' => 'nullable',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            if (count($request->staff) > 0) {

                if ($request->update == "1") {
                    $staff = ApplicationCv::where('application_id', $request->application_id)->get();
                    if (count($staff)>0) {
                        foreach ($staff as $s) {
                            ApplicationEmployer::where("application_cv_id", $s->id)->delete();
                            // ApplicationEducation::where("application_cv_id", $s->id)->delete();
                            ApplicationMembership::where("application_cv_id", $s->id)->delete();
                            ApplicationCurrentPosition::where("application_cv_id", $s->id)->delete();
                        }
                        ApplicationCv::where('application_id', $request->application_id)->delete();
                    }
                }

                foreach ($request->staff as $key => $staff) {
                    $staff_create = ApplicationCv::create([
                        'application_id'=>$request->application_id,
                        'name'=>$staff['name'],
                        // 'dob'=>$staff['dob'],
                        'language'=>$staff['language'],
                        'coren_license_number'=>$staff['coren_license_number'],
                        'coren_license_document'=>$staff['coren_license_document'],
                        // 'countries_experience'=>$staff['countries_experience'],
                        // 'work_undertaken'=>$staff['work_undertaken'],
                        'education_certificate'=>$staff['education_certificate'],
                        'professional_certificate'=>$staff['professional_certificate'],
                        'cv'=>$staff['cv'],
                    ]);
                    // Employer
                    if (count($staff['employer'])>0) {
                        foreach ($staff['employer'] as $emp) {
                            ApplicationEmployer::create([
                                'application_cv_id'=>$staff_create->id,
                                'name'=>$emp['name'],
                                'position'=>$emp['position'],
                                'start'=>$emp['start_date'],
                                'end'=>$emp['end_date'],
                                'description'=>$emp['description'],
                            ]);
                        }
                    }

                    // Education
                    // if (count($staff['education'])>0) {
                    //     foreach ($staff['education'] as $edu) {
                    //         ApplicationEducation::create([
                    //             'application_cv_id'=>$staff_create->id,
                    //             'qualification'=>$edu['qualification'],
                    //             'course'=>$edu['course'],
                    //             'school'=> $edu['school'],
                    //             'start'=>$edu['start_date'],
                    //             'end'=>$edu['end_date'],
                    //         ]);
                    //     }
                    // }

                    // Curent position
                    if ($staff['current_position']) {
                        // foreach ($staff['current_position'] as $edu) {
                            $cp = $staff['current_position'];

                            ApplicationCurrentPosition::create([
                                'application_cv_id'=>$staff_create->id,
                                'position'=>$cp['position'],
                                'start'=>$cp['start_date'],
                                'description'=>$cp['description'],
                            ]);
                        // }
                    }

                    // membership
                    // if (count($staff['membership'])>0) {
                    //     foreach ($staff['membership'] as $mem) {
                    //         ApplicationMembership::create([
                    //             'application_cv_id'=>$staff_create->id,
                    //             'rank'=>$mem['rank'],
                    //             'state'=>$mem['state'],
                    //             'date'=>$mem['date'],
                    //         ]);
                    //     }
                    // }

                    // training
                    // if (count($staff['training'])>0) {
                    //     foreach ($staff['training'] as $tr) {
                    //         ApplicationTraining::create([
                    //             'application_cv_id'=>$staff_create->id,
                    //             'course'=>$tr['course'],
                    //             'date'=>$tr['date'],
                    //         ]);
                    //     }
                    // }

                }

                return response()->json([
                    'status' => true,
                    'message' => "Successful, Staff's are added to system."
                    // 'data' => [
                    //     'application_profile' => $staff_create,
                    // ],
                ]);

            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Failed due to no staff added. try again!"
                ], 422);
            }


            
        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function createProject(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

            $validator = Validator::make($request->all(), [
                'update'=>'nullable',
                'application_id'=>'required',
                'projects' => 'nullable',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            if (count($request->projects) > 0) {

                if ($request->update == "1") {
                    $projects = ApplicationProject::where('application_id', $request->application_id)->get();
                    if (count($projects)>0) {
                        foreach ($projects as $p) {
                            ApplicationProjectReferee::where("application_project_id", $p->id)->delete();
                            ApplicationProjectSubContractor::where("application_project_id", $p->id)->delete();
                        }
                        ApplicationProject::where('application_id', $request->application_id)->delete();
                    }
                }

                foreach ($request->projects as $key => $project) {
                    $project_create = ApplicationProject::create([
                        'application_id'=>$request->application_id,
                        'name'=>$project['name'],
                        'address'=>$project['address'],
                        'date_of_contract'=>$project['date_of_contract'],
                        'employer'=>$project['employer'],
                        'location'=>$project['location'],
                        'description'=>$project['description'],
                        'date_of_completion'=>$project['date_of_completion'],
                        'project_cost'=>$project['project_cost'],
                        'role_of_applicant'=>$project['role_of_applicant'],
                        // 'equity'=>$project['equity'],
                        // 'implemented'=>$project['implemented'],
                        'geocoordinate'=>$project['geocoordinate'],
                        'subcontactor_role'=>$project['subcontractor_role'],
                        'award_letter'=>$project['award_letter'],
                        'interim_valuation_cert'=>$project['interim_valuation_cert'],
                        'certificate_of_completion'=>$project['certificate_of_completion'],
                        'evidence_of_completion'=>$project['evidence_of_completion'],
                    ]);

                    // Referees
                    if (count($project['referee'])>0) {
                        foreach ($project['referee'] as $ref) {
                            ApplicationProjectReferee::create([
                                'application_project_id'=>$project_create->id,
                                'name'=>$ref['name'],
                                'phone'=>$ref['phone'],
                            ]);
                        }
                    }

                    // subcontractor
                    if (count($project['subcontractor'])>0) {
                        foreach ($project['subcontractor'] as $sub) {
                            ApplicationProjectSubContractor::create([
                                'application_project_id'=>$project_create->id,
                                'name'=>$sub['name'],
                                'address'=>$sub['address'],
                            ]);
                        }
                    }
                }

                return response()->json([
                    'status' => true,
                    'message' => "Successful, project's are added to the application."
                    
                ]);

            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Failed, Due to no projects were added. try again!"
                ], 422);
            }
  
        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function createFinancial(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

            $validator = Validator::make($request->all(), [
                'update'=>'nullable',
                'application_id'=>'required',
                'financial_info' => 'required',
                'financial_dept_info' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            if ($request->update == "1") {
                ApplicationFinancialInfo::where("application_id", $request->application_id)->delete();
                ApplicationFinancialDebtInfo::where("application_id", $request->application_id)->delete();
            }

            $fy1 = $request->financial_info['fy1'];
            $fy2 = $request->financial_info['fy2'];
            $fy3 = $request->financial_info['fy3'];

            $fy1['application_id'] = $request->application_id;
            $fy1['type'] = "fy1";

            $fy2['application_id'] = $request->application_id;
            $fy2['type'] = "fy2";

            $fy3['application_id'] = $request->application_id;
            $fy3['type'] = "fy3";

            ApplicationFinancialInfo::create($fy1);
            ApplicationFinancialInfo::create($fy2);
            ApplicationFinancialInfo::create($fy3);

            // $dept = $request->financial_dept_info;

            foreach ($request->financial_dept_info as $dept) {
                $dept_create = ApplicationFinancialDebtInfo::create([
                    'application_id'=>$request->application_id,
                    'project_name'=> $dept['project_name'],
                    'location'=> $dept['location'],
                    'sector'=> $dept['sector'],
                    'aggregate_amount'=> $dept['aggregate_amount'],
                    'date_of_financial_close'=> $dept['date_of_financial_close'],
                    // 'date_of_first_drawdown'=> $dept['date_of_first_drawdown'],
                    // 'date_of_final_drawdown'=> $dept['date_of_final_drawdown'],
                    // 'tenor_of_financing'=> $dept['tenor_of_financing'],
                    'evidence_of_support'=> $dept['evidence_of_support'],
                ]);
    
                $borrower = ApplicationFinancialDebtInfoBorrower::create([
                    'application_financial_debt_id'=>$dept_create->id,
                    'name'=> $dept['borrower']['name'],
                    // 'rc_number'=> $dept['borrower']['rc_number'],
                    'address'=> $dept['borrower']['address'],
                ]);
            }


            return response()->json([
                'status' => true,
                'message' => "Successful, Financial info are added to the application."

            ]);
            
        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function createDocument(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

            $validator = Validator::make($request->all(), [
                'update'=>'nullable',
                'application_id'=>'required',
                'documents' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            if ($request->update == "1") {
                $dd = ApplicationDocument::where("application_id", $request->application_id)->get();
                if (count($dd)>0) {
                    ApplicationDocument::where("application_id", $request->application_id)->delete();
                }
            }

            foreach ($request->documents as $key => $doc) {
                $docc = ApplicationDocument::create([
                    "application_id"=>$request->application_id,
                    "name"=>$doc['name'],
                    "url"=>$doc['url'],
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => "Successful, Documents are added to application."

            ]);
            
        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }





    public function uploadStaff(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

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
                $request->file("file")->storeAs("public/staffFiles", $fileNameToStore);

                $url = url('/storage/staffFiles/'.$fileNameToStore);

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
                ], 422);
            }

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function uploadProject(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

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
                $request->file("file")->storeAs("public/projectFiles", $fileNameToStore);

                $url = url('/storage/projectFiles/'.$fileNameToStore);

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
                ], 422);
            }

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function uploadDocument(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

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
                $request->file("file")->storeAs("public/documentFiles", $fileNameToStore);

                $url = url('/storage/documentFiles/'.$fileNameToStore);

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
                ], 422);
            }

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function uploadFinancial(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

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
                $request->file("file")->storeAs("public/financialFiles", $fileNameToStore);

                $url = url('/storage/financialFiles/'.$fileNameToStore);

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
                ], 422);
            }

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function uploadProfile(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

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
                $request->file("file")->storeAs("public/profileFiles", $fileNameToStore);

                $url = url('/storage/profileFiles/'.$fileNameToStore);

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
                ], 422);
            }

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function submit(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

            $validator = Validator::make($request->all(), [
                'application_id'=>'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $application = Application::where('id', $request->application_id)->get();

            Application::where('id', $request->application_id)->update([
                "status"=>"1"
            ]);

            if (!$application[0]->status == 1) {
                $mailData = [
                    'title' => 'REA - Application update',
                    'body' => "Dear ".$request->user()->name." with ".$request->user()->email.", \nYour application for ".$application[0]->program->name." has been successfuly submited, And you have a window to edit your application before the deadline as shown on the portal. \nThank you.",
                ];
                
                Mail::to($request->user()->email)->send(new MessageNotificationMail($mailData));
            }else{
                $mailData = [
                    'title' => 'REA - Application update',
                    'body' => "Dear ".$request->user()->name." with ".$request->user()->email.", \nYour application for ".$application[0]->program->name." has been updated, And you still have a window to edit your application before the deadline. \nThank you.",
                ];
                
                Mail::to($request->user()->email)->send(new MessageNotificationMail($mailData));
            }

            $app = Application::find($request->application_id);

            return response()->json([
                'status' => true,
                'message' => "Successful,  Application added.",
                'data' => [
                    "application"=>$app
                ]
            ]);
            
        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }


    public function getApplication(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

            $validator = Validator::make($request->all(), [
                'program_id'=>'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $app = Application::where(['applicant_id'=> $request->user()->id, 'program_id'=>$request->program_id])->with("sublots")->get();
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
    

    public function getApplicationProgress(Request $request) 
    {
        if ($request->user()->tokenCan('Applicant')) {

            $validator = Validator::make($request->all(), [
                'program_id'=>'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $app = Application::where(['applicant_id'=> $request->user()->id, 'program_id'=>$request->program_id])->with("sublots")->get();
            if (count($app)>0) {
                $app = $app[0];

                $data = [
                    "pre_qualification"=> ['status'=> 0, 'msg'=>''],
                    "lots"=> ['status'=> 0, 'msg'=>''],
                    "sublots"=>['status'=> 0, 'msg'=>''],
                    "eligibility_requirement"=> ['status'=> 0, 'msg'=>''],
                    "technical_requirement"=> ['status'=> 0, 'msg'=>''],
                    "financial_info"=> ['status'=> 0, 'msg'=>''],
                ];

                $app_profile = ApplicationProfile::where(["application_id"=>$app->id])->with('contact_persons')->with('share_holders')->get();
                $app_staff = ApplicationCv::where(["application_id"=>$app->id])->with('employers')->with('current_position')->get();

                $app_projects = ApplicationProject::where(["application_id"=>$app->id])->with('referees')->with('sub_contractors')->get();
                
                $app_fin = ApplicationFinancialInfo::where(["application_id"=>$app->id])->get();
                $app_fin_dept = ApplicationFinancialDebtInfo::where(["application_id"=>$app->id])->with('borrowers')->get();

                $fin = [
                    "financial_info" => $app_fin,
                    "financial_dept_info" => $app_fin_dept
                ];
                
                $app_docs = ApplicationDocument::where(["application_id"=>$app->id])->get();
                $sublots = DB::table('application_sub_lot')->where('application_id', $app->id)->get();

                // progress for pre_qualification
                if ($app->pre_qualification_status == 1) {
                    $data['pre_qualification']['status'] = 1;
                    $data['pre_qualification']['msg'] = "Completed";
                }else{
                    $data['pre_qualification']['status'] = 0;
                    $data['pre_qualification']['msg'] = "you have to agree to the PreQualification document.";
                }
                // End of  progress for pre_qualification


                // progress for sub lots
                if (count($sublots)>0) {
                    $data['lots']['status'] = 1;
                    $data['sublots']['status'] = 1;
                    $num = count($sublots);
                    if ($num < 4) {
                        $data['lots']['msg'] = "";
                        $data['sublots']['msg'] = "You have added $num Sub Lots only, you can add up-to 4.";
                    }else{
                        $data['lots']['msg'] = "Completed";
                        $data['sublots']['msg'] = "Completed";
                    }
                }else{
                    $data['lots']['status'] = 0;
                    $data['sublots']['status'] = 0;
                    $data['lots']['msg'] = "You can add not more than 2 Lots & 4 Sublots.";
                    $data['sublots']['msg'] = "You can add not more than 2 Lots & 4 Sublots.";
                }
                // end of progress for sub lots

                // progress for eligibility requirement
                $s_ap = 0;
                if (count($app_profile) > 0) {
                    $app_p = $app_profile[0];
                    if ((!$app_p->name==null) && (!$app_p->registration_date==null) && (count($app_p->contact_persons)>0) && (count($app_p->share_holders)>0)) {
                        $s_ap = 1;
                        $data['eligibility_requirement']['msg'] .= " Your Profile is completed";
                    }else {
                        $s_ap = 0;
                        $data['eligibility_requirement']['msg'] .= " You're still about to commplete the requirements";
                    }
                }else {
                    $s_ap = 0;
                    $data['eligibility_requirement']['msg'] .= " You need to add APPLICANT NAME & DATE OF INCORPORATION/REGISTRATION";
                }
                $s_ad = 0;
                if (count($app_docs) > 0) {
                    if (count($app_docs)<12) {
                        $s_ad = 0;
                        $data['eligibility_requirement']['msg'] .= " and the document uploades are not complete.";
                    }else{
                        $s_ad = 1;
                        $data['eligibility_requirement']['msg'] .= ".";
                    }
                }else {
                    $s_ad = 0;
                    $data['eligibility_requirement']['msg'] .= " and you have not uploaded documents yet.";
                }

                    // checking if its completed
                if (($s_ad == 1) && ($s_ap ==1)) {
                    $data['eligibility_requirement']['status'] = 1;
                    $data['eligibility_requirement']['msg'] = "Completed";
                }else{
                    $data['eligibility_requirement']['status'] = 0;
                }
                // end of progress for eligibility requirement

                // progress for Technical requirement
                $s_apf = 0;
                if (count($app_profile) > 0) {
                    $app_p = $app_profile[0];
                    if ((!$app_p->description==null) && (!$app_p->website==null) && (!$app_p->evidence_of_equipment_ownership==null)) {
                        $s_apf = 1;
                        $data['technical_requirement']['msg'] .= " Your Profile technical requirement is complete";
                    }else {
                        $s_apf = 0;
                        $data['technical_requirement']['msg'] .= " You're still about to commplete your profile technical requirements";
                    }
                }else {
                    $s_apf = 0;
                    $data['technical_requirement']['msg'] .= " You need to go back to 'ELIGIBILITY REQUIREMENTS' tab and add APPLICANT NAME & DATE OF INCORPORATION/REGISTRATION";
                }

                $s_as = 0;
                if (count($app_staff) > 0) {
                    $s_as = 1;
                    $data['technical_requirement']['msg'] .= "";
                }else {
                    $s_as = 0;
                    $data['technical_requirement']['msg'] .= ", You need to add atleast one employer";
                }

                $s_apr = 0;
                if (count($app_projects) > 0) {
                    $s_apr = 1;
                    $data['technical_requirement']['msg'] .= "";
                }else {
                    $s_apr = 0;
                    $data['technical_requirement']['msg'] .= ", You need to add atleast one project";
                }

                // checking if its completed
                if (($s_apf == 1) && ($s_as == 1) && ($s_apr == 1)) {
                    $data['technical_requirement']['status'] = 1;
                    $data['technical_requirement']['msg'] = "Completed";
                }else{
                    $data['technical_requirement']['status'] = 0;
                }
                // End of tech requirement


                // progress for Financial Info
                $s_asf = 0;
                if (count($app_fin) > 0) {
                    if (count($app_fin)<3) {
                        $s_asf = 0;
                    $data['financial_info']['msg'] .= "You to add all the fields";
                    }else {
                        $s_asf = 1;
                        $data['financial_info']['msg'] .= "";
                    }
                }else {
                    $s_asf = 0;
                    $data['financial_info']['msg'] .= "You need to add your financial info.";
                }

                $s_aprf = 0;
                if (count($app_fin_dept) > 0) {
                    $s_aprf = 1;
                    $data['financial_info']['msg'] .= "";
                }else {
                    $s_aprf = 0;
                    $data['financial_info']['msg'] .= "You need to add atleast financial dept";
                }

                // checking if its completed
                if (($s_asf == 1) && ($s_aprf == 1)) {
                    $data['financial_info']['status'] = 1;
                    $data['financial_info']['msg'] = "Completed";
                }else{
                    $data['financial_info']['status'] = 0;
                }
                // End of Financial Info

                return response()->json([
                    'status' => true,
                    'message' => "Successful.",
                    'data' => $data,
                ]);   
                 
            }else{
                $data = [
                    "pre_qualification"=> ['status'=> 0, 'msg'=>''],
                    "lots"=> ['status'=> 0, 'msg'=>''],
                    "sublots"=>['status'=> 0, 'msg'=>''],
                    "eligibility_requirement"=> ['status'=> 0, 'msg'=>''],
                    "technical_requirement"=> ['status'=> 0, 'msg'=>''],
                    "financial_info"=> ['status'=> 0, 'msg'=>''],
                ];
                return response()->json([
                    'status' => false,
                    'data'=> $data,
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

    public function pre_qualification(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

            $validator = Validator::make($request->all(), [
                'application_id'=>'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            Application::where('id', $request->application_id)->update([
                "pre_qualification_status"=>"1"
            ]);

            $app = Application::find($request->application_id);

            return response()->json([
                'status' => true,
                'message' => "Successful, You've accepted the pre-qualification document.",
                'data' => [
                    "application"=>$app
                ]
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }
}
