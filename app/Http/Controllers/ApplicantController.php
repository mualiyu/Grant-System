<?php

namespace App\Http\Controllers;

use App\Mail\AcceptApplicantMail;
use App\Models\Applicant;
use App\Models\JV;
use App\Models\User;
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
            'cac_certificate' => 'nullable|max:9000',
            'tax_clearance_certificate' => 'nullable|max:9000',
            'has_designed'=>'required',
            'has_operated'=>'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        if ($request->hasFile("cac_certificate")) {
            $fileNameWExt = $request->file("cac_certificate")->getClientOriginalName();
            $fileName = pathinfo($fileNameWExt, PATHINFO_FILENAME);
            $fileExt = $request->file("cac_certificate")->getClientOriginalExtension();
            $fileNameToStore = $fileName."_".time().".".$fileExt;
            $request->file("cac_certificate")->storeAs("public/profileFiles", $fileNameToStore);

            $url = url('/storage/profileFiles/'.$fileNameToStore);
            $request['cac_certificate'] = $url;

        }else{
            $request['cac_certificate'] = "";
        }

        if ($request->hasFile("tax_clearance_certificate")) {
            $fileNameWExt = $request->file("tax_clearance_certificate")->getClientOriginalName();
            $fileName = pathinfo($fileNameWExt, PATHINFO_FILENAME);
            $fileExt = $request->file("tax_clearance_certificate")->getClientOriginalExtension();
            $fileNameToStore = $fileName."_".time().".".$fileExt;
            $request->file("tax_clearance_certificate")->storeAs("public/profileFiles", $fileNameToStore);

            $url = url('/storage/profileFiles/'.$fileNameToStore);
            $request['tax_clearance_certificate'] = $url;

        }else{
            $request['tax_clearance_certificate'] = "";
        }

        // $request['password'] = Hash::make($request->password);

        $pass = mt_rand(10000000,99999999);

        $password = Hash::make($pass);

        $request['password'] = $password;
        $request['isApproved'] = 1;

        $user = Applicant::create($request->all());

        $mailData = [
            'title' => 'Your registration is successful.',
            'body' => 'Use Username: '.$user->username.' & Password: '.$pass,
            // 'body'=> "Congratulations on your successful registration! We are currently reviewing applicants and will notify you soon regarding the Approval decision.
            // \nOnce the selection is made, you will receive an acceptance notification along with your password for accessing your portal and resources.
            // \nThank you for your patience. We will keep you updated.
            // \nBest regards,",
        ];
        
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

        if (!$user || !Hash::check($request->password, $user->password) || $user->isApproved==0) {
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
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $applicant = Applicant::where('username', '=', $request->username)->get();
        if (!count($applicant)>0) {
            $applicant = Applicant::where('email', '=', $request->username)->get();
        }else{
            $applicant = Applicant::where('username', '=', $request->username)->get();
        }
        $username = $request->user;
        if (is_numeric($username) == true) {
            $user = Applicant::where('phone', '=', $username)->get();
        } else {
            $user = Applicant::where('username', '=', $username)->get();
        }
        if (count($applicant) > 0) {

            $pass = mt_rand(10000000, 99999999);

            $password = Hash::make($pass);

            $user = $applicant[0];

            $mailData = [
                    'title' => 'Your password reset',
                    'body' => 'Use Username: ' . $user->username . ' & Password: ' . $pass,
                ];
                
            $update = Applicant::where('id', '=', $applicant[0]->id)->update([
                'password' => $password
            ]);
                
            if ($update) {
                return "Your username is: ".$user->username." & password is: ".$pass;

                // Mail::to($user->email)->send(new AcceptApplicantMail($mailData));
                return response()->json([
                    'status' => true,
                    'message' => "An email is sent to your mail. Thank you!"
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "System Error, Failed to change password. Try again later."
                ], 422);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => "Failed, User not found"
            ], 422);
        }
    }

    //reset
    public function reset(Request $request)
    {
     if ($request->user()->tokenCan('Applicant')) {
            $validator = Validator::make($request->all(), [
                'current_password' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $applicant = Applicant::where('id', '=', $request->user()->id)->get();

            if(count($applicant)>0){
                if (Hash::check($request->current_password, $applicant[0]->password)) {

                        $update = Applicant::where('id', '=', $applicant[0]->id)->update([
                            'password' => Hash::make($request->password),
                        ]);
            
                        if ($update) {
                            return response()->json([
                                'status' => true,
                                'message' => "You've successfully changed your password."
                            ], 200);
                        }else{
                            return response()->json([
                                'status' => false,
                                'message' => "System Error, Failed to change password. Try again later."
                            ], 422);
                        }
                    
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Failed, Password does not match the current password"
                    ], 422);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Failed, User not found"
                ], 422);
            }

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }   
    }

    //user
    public function user(Request $request)
    {
        $user = Applicant::where('id', $request->user()->id)->with('jvs')->get()[0];
        if ($request->user()->tokenCan('Applicant')) {
            return response()->json([
                'status' => true,
                'data' => [
                    'user' => $user
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
    
    public function updateProfile(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'phone' => 'required',
                'person_incharge' => 'nullable',
                'rc_number' => 'required',
                'address' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $user = Applicant::where('id',$request->user()->id)->update($request->all());

            if ($user) {
                # code...
                return response()->json([
                    'status' => true,
                    'message' => "Profile update completed",
                    // 'data' => [
                    //     'user' => User::find($request->user()->id),
                    // ],
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Sorry Profile Update Failed"
                ], 422);
            }

        }else{
            // $request->user()->tokens()->delete();
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function addJv(Request $request)
    {
        if ($request->user()->tokenCan('Applicant')) {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'phone' => 'required',
                'email' => "required",
                'rc_number' => 'nullable',
                'address' => 'nullable',
                'document'=> 'nullable',
                'type'=> 'nullable',

                'evidence_of_cac'=> 'nullable',
                'company_income_tax'=> 'nullable',
                'audited_account'=> 'nullable',
                'letter_of_authorization'=> 'nullable',
                'sworn_affidavits'=> 'nullable',
            ]);

            $evidence_of_cac = '';

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            if ($request->hasFile("document")) {
                $fileNameWExt = $request->file("document")->getClientOriginalName();
                $fileName = pathinfo($fileNameWExt, PATHINFO_FILENAME);
                $fileExt = $request->file("document")->getClientOriginalExtension();
                $fileNameToStore = $fileName."_".time().".".$fileExt;
                $request->file("document")->storeAs("public/jvFiles", $fileNameToStore);

                $document = url('/storage/jvFiles/'.$fileNameToStore);
            }else{
                $document = '';
            }

            if ($request->hasFile("evidence_of_cac")) {
                $fileNameWExt = $request->file("evidence_of_cac")->getClientOriginalName();
                $fileName = pathinfo($fileNameWExt, PATHINFO_FILENAME);
                $fileExt = $request->file("evidence_of_cac")->getClientOriginalExtension();
                $fileNameToStore = $fileName."_".time().".".$fileExt;
                $request->file("evidence_of_cac")->storeAs("public/jvFiles", $fileNameToStore);

                $evidence_of_cac = url('/storage/jvFiles/'.$fileNameToStore);
            }else{
                $evidence_of_cac = '';
            }

            if ($request->hasFile("company_income_tax")) {
                $fileNameWExt = $request->file("company_income_tax")->getClientOriginalName();
                $fileName = pathinfo($fileNameWExt, PATHINFO_FILENAME);
                $fileExt = $request->file("company_income_tax")->getClientOriginalExtension();
                $fileNameToStore = $fileName."_".time().".".$fileExt;
                $request->file("company_income_tax")->storeAs("public/jvFiles", $fileNameToStore);

                $company_income_tax = url('/storage/jvFiles/'.$fileNameToStore);
            }else{
                $company_income_tax = '';
            }

            if ($request->hasFile("audited_account")) {
                $fileNameWExt = $request->file("audited_account")->getClientOriginalName();
                $fileName = pathinfo($fileNameWExt, PATHINFO_FILENAME);
                $fileExt = $request->file("audited_account")->getClientOriginalExtension();
                $fileNameToStore = $fileName."_".time().".".$fileExt;
                $request->file("audited_account")->storeAs("public/jvFiles", $fileNameToStore);

                $audited_account = url('/storage/jvFiles/'.$fileNameToStore);
            }else{
                $audited_account = '';
            }

            if ($request->hasFile("letter_of_authorization")) {
                $fileNameWExt = $request->file("letter_of_authorization")->getClientOriginalName();
                $fileName = pathinfo($fileNameWExt, PATHINFO_FILENAME);
                $fileExt = $request->file("letter_of_authorization")->getClientOriginalExtension();
                $fileNameToStore = $fileName."_".time().".".$fileExt;
                $request->file("letter_of_authorization")->storeAs("public/jvFiles", $fileNameToStore);

                $letter_of_authorization = url('/storage/jvFiles/'.$fileNameToStore);
            }else{
                $letter_of_authorization = '';
            }

            if ($request->hasFile("sworn_affidavits")) {
                $fileNameWExt = $request->file("sworn_affidavits")->getClientOriginalName();
                $fileName = pathinfo($fileNameWExt, PATHINFO_FILENAME);
                $fileExt = $request->file("sworn_affidavits")->getClientOriginalExtension();
                $fileNameToStore = $fileName."_".time().".".$fileExt;
                $request->file("sworn_affidavits")->storeAs("public/jvFiles", $fileNameToStore);

                $sworn_affidavits = url('/storage/jvFiles/'.$fileNameToStore);
            }else{
                $sworn_affidavits = '';
            }

            $request['applicant_id'] = $request->user()->id;

            // $request['document'] = $document;
            // $request['evidence_of_cac'] = $evidence_of_cac;
            // $request['company_income_tax'] = $company_income_tax;
            // $request['audited_account'] = $audited_account;
            // $request['letter_of_authorization'] = $letter_of_authorization;
            // $request['sworn_affidavits'] = $sworn_affidavits;

            // return $request->all();
            $jv = JV::create([
                'applicant_id'=> $request->user()->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'rc_number' => $request->rc_number,
                'address' => $request->address,
                'document'=> $document,
                'type'=> $request->type,

                'evidence_of_cac'=> $evidence_of_cac,
                'company_income_tax'=> $company_income_tax,
                'audited_account'=> $audited_account,
                'letter_of_authorization'=> $letter_of_authorization,
                'sworn_affidavits'=> $sworn_affidavits,
            ]);

            if ($jv) {
                
                return response()->json([
                    'status' => true,
                    'message' => "Join Venture is added to system info",
                    'data' => [
                        'jv' => $jv,
                    ],
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Sorry Failed to add JV"
                ], 422);
            }

        }else{
            // $request->user()->tokens()->delete();
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function updateJv(Request $request, $id)
    {
        if ($request->user()->tokenCan('Applicant')) {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'phone' => 'required',
                'email' => "required",
                'rc_number' => 'nullable',
                'address' => 'nullable',
                'document'=> 'nullable',
                'type'=>'nullable',

                'evidence_of_cac'=> 'nullable',
                'company_income_tax'=> 'nullable',
                'audited_account'=> 'nullable',
                'letter_of_authorization'=> 'nullable',
                'sworn_affidavits'=> 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            if ($request->hasFile("document")) {
                $fileNameWExt = $request->file("document")->getClientOriginalName();
                $fileName = pathinfo($fileNameWExt, PATHINFO_FILENAME);
                $fileExt = $request->file("document")->getClientOriginalExtension();
                $fileNameToStore = $fileName."_".time().".".$fileExt;
                $request->file("document")->storeAs("public/jvFiles", $fileNameToStore);

                $document = url('/storage/jvFiles/'.$fileNameToStore);
            }else{
                $document = '';
            }

            if ($request->hasFile("evidence_of_cac")) {
                $fileNameWExt = $request->file("evidence_of_cac")->getClientOriginalName();
                $fileName = pathinfo($fileNameWExt, PATHINFO_FILENAME);
                $fileExt = $request->file("evidence_of_cac")->getClientOriginalExtension();
                $fileNameToStore = $fileName."_".time().".".$fileExt;
                $request->file("evidence_of_cac")->storeAs("public/jvFiles", $fileNameToStore);

                $evidence_of_cac = url('/storage/jvFiles/'.$fileNameToStore);
            }else{
                $evidence_of_cac = '';
            }

            if ($request->hasFile("company_income_tax")) {
                $fileNameWExt = $request->file("company_income_tax")->getClientOriginalName();
                $fileName = pathinfo($fileNameWExt, PATHINFO_FILENAME);
                $fileExt = $request->file("company_income_tax")->getClientOriginalExtension();
                $fileNameToStore = $fileName."_".time().".".$fileExt;
                $request->file("company_income_tax")->storeAs("public/jvFiles", $fileNameToStore);

                $company_income_tax = url('/storage/jvFiles/'.$fileNameToStore);
            }else{
                $company_income_tax = '';
            }

            if ($request->hasFile("audited_account")) {
                $fileNameWExt = $request->file("audited_account")->getClientOriginalName();
                $fileName = pathinfo($fileNameWExt, PATHINFO_FILENAME);
                $fileExt = $request->file("audited_account")->getClientOriginalExtension();
                $fileNameToStore = $fileName."_".time().".".$fileExt;
                $request->file("audited_account")->storeAs("public/jvFiles", $fileNameToStore);

                $audited_account = url('/storage/jvFiles/'.$fileNameToStore);
            }else{
                $audited_account = '';
            }

            if ($request->hasFile("letter_of_authorization")) {
                $fileNameWExt = $request->file("letter_of_authorization")->getClientOriginalName();
                $fileName = pathinfo($fileNameWExt, PATHINFO_FILENAME);
                $fileExt = $request->file("letter_of_authorization")->getClientOriginalExtension();
                $fileNameToStore = $fileName."_".time().".".$fileExt;
                $request->file("letter_of_authorization")->storeAs("public/jvFiles", $fileNameToStore);

                $letter_of_authorization = url('/storage/jvFiles/'.$fileNameToStore);
            }else{
                $letter_of_authorization = '';
            }

            if ($request->hasFile("sworn_affidavits")) {
                $fileNameWExt = $request->file("sworn_affidavits")->getClientOriginalName();
                $fileName = pathinfo($fileNameWExt, PATHINFO_FILENAME);
                $fileExt = $request->file("sworn_affidavits")->getClientOriginalExtension();
                $fileNameToStore = $fileName."_".time().".".$fileExt;
                $request->file("sworn_affidavits")->storeAs("public/jvFiles", $fileNameToStore);

                $sworn_affidavits = url('/storage/jvFiles/'.$fileNameToStore);
            }else{
                $sworn_affidavits = '';
            }

            // $request['document'] = $document;
            // $request['evidence_of_cac'] = $evidence_of_cac;
            // $request['company_income_tax'] = $company_income_tax;
            // $request['audited_account'] = $audited_account;
            // $request['letter_of_authorization'] = $letter_of_authorization;
            // $request['sworn_affidavits'] = $sworn_affidavits;

            $jv = JV::where(['id'=>$id, 'applicant_id'=>$request->user()->id])->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'rc_number' => $request->rc_number,
                'address' => $request->address,
                'document'=> $document,
                'type'=> $request->type,

                'evidence_of_cac'=> $evidence_of_cac,
                'company_income_tax'=> $company_income_tax,
                'audited_account'=> $audited_account,
                'letter_of_authorization'=> $letter_of_authorization,
                'sworn_affidavits'=> $sworn_affidavits,
            ]);

            if ($jv) {
                
                return response()->json([
                    'status' => true,
                    'message' => "Join Venture is updated",
                    // 'data' => [
                    //     'jv' => $jv,
                    // ],
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Sorry Failed to update JV"
                ], 422);
            }

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
