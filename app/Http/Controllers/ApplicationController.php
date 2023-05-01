<?php

namespace App\Http\Controllers;

use App\Models\Application;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                'application_id' => 'nullable'
                
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
                'brief_description'=> 'nullable',
                'website'=> 'nullable',
                // 'cac_number'=> 'required',
                'share_holders'=> 'nullable',
                'ultimate_owner'=> 'required',
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
                    'description' => $request->brief_description,
                    'website' => $request->website,
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
                    'description' => $request->brief_description,
                    'website' => $request->website,
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


    public function createStaff(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

            $validator = Validator::make($request->all(), [
                'update'=>'nullable',
                'application_id'=>'required',
                'staff' => 'required',
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
                            ApplicationEducation::where("application_cv_id", $s->id)->delete();
                            ApplicationMembership::where("application_cv_id", $s->id)->delete();
                            ApplicationTraining::where("application_cv_id", $s->id)->delete();
                        }
                        ApplicationCv::where('application_id', $request->application_id)->delete();
                    }
                }

                foreach ($request->staff as $key => $staff) {
                    $staff_create = ApplicationCv::create([
                        'application_id'=>$request->application_id,
                        'name'=>$staff['name'],
                        'dob'=>$staff['dob'],
                        'language'=>$staff['language'],
                        'nationality'=>$staff['nationality'],
                        'countries_experience'=>$staff['countries_experience'],
                        'work_undertaken'=>$staff['work_undertaken'],
                        'education_certificate'=>$staff['education_certificate'],
                        'professional_certificate'=>$staff['professional_certificate'],
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
                            ]);
                        }
                    }

                    // Education
                    if (count($staff['education'])>0) {
                        foreach ($staff['education'] as $edu) {
                            ApplicationEducation::create([
                                'application_cv_id'=>$staff_create->id,
                                'qualification'=>$edu['qualification'],
                                'course'=>$edu['course'],
                                'school'=> $edu['school'],
                                'start'=>$edu['start_date'],
                                'end'=>$edu['end_date'],
                            ]);
                        }
                    }

                    // membership
                    if (count($staff['membership'])>0) {
                        foreach ($staff['membership'] as $mem) {
                            ApplicationMembership::create([
                                'application_cv_id'=>$staff_create->id,
                                'rank'=>$mem['rank'],
                                'state'=>$mem['state'],
                                'date'=>$mem['date'],
                            ]);
                        }
                    }

                    // training
                    if (count($staff['training'])>0) {
                        foreach ($staff['training'] as $tr) {
                            ApplicationTraining::create([
                                'application_cv_id'=>$staff_create->id,
                                'course'=>$tr['course'],
                                'date'=>$tr['date'],
                            ]);
                        }
                    }

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
                'projects' => 'required',
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
                        'equity'=>$project['equity'],
                        'implemented'=>$project['implemented'],
                        'subcontactor_role'=>$project['subcontractor_role'],
                        'award_letter'=>$project['award_letter'],
                        'interim_valuation_cert'=>$project['interim_valuation_cert'],
                        'certificate_of_completion'=>$project['certificate_of_completion'],
                        'evidence_of_equity'=>$project['evidence_of_equity'],
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

            $dept = $request->financial_dept_info;

            $dept_create = ApplicationFinancialDebtInfo::create([
                'application_id'=>$request->application_id,
                'project_name'=> $dept['project_name'],
                'location'=> $dept['location'],
                'sector'=> $dept['sector'],
                'aggregate_amount'=> $dept['aggregate_amount'],
                'date_of_financial_close'=> $dept['date_of_financial_close'],
                'date_of_first_drawdown'=> $dept['date_of_first_drawdown'],
                'date_of_final_drawdown'=> $dept['date_of_final_drawdown'],
                'tenor_of_financing'=> $dept['tenor_of_financing'],
            ]);

            $borrower = ApplicationFinancialDebtInfoBorrower::create([
                'application_financial_debt_id'=>$dept_create->id,
                'name'=> $dept['borrower']['name'],
                'rc_number'=> $dept['borrower']['rc_number'],
                'address'=> $dept['borrower']['address'],
            ]);

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
                ApplicationDocument::where("application_id", $request->application_id)->delete();
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

            Application::where('id', $request->application_id)->update([
                "status"=>"1"
            ]);

            $app = Application::find($request->application_id);

            return response()->json([
                'status' => true,
                'message' => "Successful, Documents are added to application.",
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

            $app = Application::where(['applicant_id'=> $request->user()->id, 'program_id'=>$request->program_id])->get();
            if (count($app)>0) {
                $app = $app[0];

                $app_profile = ApplicationProfile::where(["application_id"=>$app->id])->with('contact_persons')->with('share_holders')->get();
                $app_staff = ApplicationCv::where(["application_id"=>$app->id])->with('educations')->with('memberships')->with('trainings')->with('employers')->get();

                $app_projects = ApplicationProject::where(["application_id"=>$app->id])->with('referees')->with('sub_contractors')->get();
                
                $app_fin = ApplicationFinancialInfo::where(["application_id"=>$app->id])->get();
                $app_fin_dept = ApplicationFinancialDebtInfo::where(["application_id"=>$app->id])->with('borrowers')->get();
                $fin = [
                    "financial_info" => $app_fin,
                    "financial_dept_info" => $app_fin_dept
                ];

                $app_docs = ApplicationDocument::where(["application_id"=>$app->id])->get();

                $app['application_profile'] = count($app_profile)>0 ? $app_profile: [];
                $app['application_staff'] = $app_staff;
                $app['application_projects'] = $app_projects;

                $app['application_financials'] = $fin;
                $app['application_documents'] = $app_docs;

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
    

}