<?php

namespace App\Http\Controllers;

use App\Mail\MessageNotificationMail;
use App\Models\Applicant;
use App\Models\Message;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function getAll(Request $request, Program $program)
    {
         if ($request->user()->tokenCan('Admin')) {            
             $messages = Message::where(['program_id'=>$program->id])->get();
             if (count($messages)>0) {
                 $app = [];
                 foreach ($messages as $k => $m) {
                    array_push($app, $m->applicant_id);
                 }
                 $as = array_unique($app);
                 $aa = [];
                 foreach ($as as $a) {
                     $applicant = Applicant::find($a);

                     $appMsg = Message::where(['applicant_id'=>$a, 'program_id'=>$program->id])->orderBy('created_at', 'DESC')->get();
                     $read = Message::where(['applicant_id'=>$a, 'program_id'=>$program->id, 'status'=>'1'])->get();
                     $unread = Message::where(['applicant_id'=>$a, 'program_id'=>$program->id, 'status'=>'0'])->get();

                     $applicantMessage = [
                        'applicantId'=> $a,
                        'name'=> $applicant->name,
                        'unread'=> count($unread),
                        'read'=> count($read),
                        'lastMessage'=> $appMsg[0]->created_at,
                        'messages'=> $appMsg,
                     ];
                     array_push($aa, $applicantMessage);

                 }

                 return response()->json([
                     'status' => true,
                     'message' => "success you have about ". count($aa). " users in your chat history",
                     'data' => [
                         'message' => $aa,
                     ],
                 ]);
             }else {
                return response()->json([
                    'status' => false,
                    'message' => "No messages found for this program..."
                ], 404);
             }
        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function adminSend(Request $request, Program $program)
    {
         if ($request->user()->tokenCan('Admin')) {

            $validator = Validator::make($request->all(), [
                'applicant_id' => 'required',
                'msg' => 'nullable',
                'file' => 'nullable|max:9000',
            ]);

            $applicant = Applicant::find($request->applicant_id);

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
                $request->file("file")->storeAs("public/messageFiles", $fileNameToStore);

                $urlFile = url('/storage/messageFiles/'.$fileNameToStore);
            }else{
                $urlFile = '';
            }
            $msg = Message::create([
                'program_id'=>$program->id,
                'applicant_id'=>$request->applicant_id,
                'from'=>'Admin',
                'to'=>$request->applicant_id,
                'msg'=>$request->msg,
                'type'=>'programMessage',
                'status'=>'0',
                'file'=>$urlFile,
            ]);

            $mailData = [
                'title' => 'Message Notification',
                'body' => "Dear $applicant->name, \nYou have a new message from Admin, kindly check the message tab in the Program home of which you applied for, \nThank you.",
            ];
            
            Mail::to($applicant->email)->send(new MessageNotificationMail($mailData));

            return response()->json([
                'status' => true,
                'message' => "Message sent successful.",
                'data' => [
                    'message' => $msg,
                ],
            ]);

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function applicantGetAll(Request $request, Program $program)
    {
        if ($request->user()->tokenCan('Applicant')) {            
             $appMsg = Message::where(['applicant_id'=>$request->user()->id, 'program_id'=>$program->id])->orderBy('created_at', 'DESC')->get();
             if (count($appMsg)>0) {
                return response()->json([
                    'status' => true,
                    'message' => "you have about ".count($appMsg). " messages in your chat box",
                    'data' => [
                        'messages' => $appMsg,
                    ],
                ]);
             }else {
                return response()->json([
                    'status' => false,
                    'message' => "No messages found for this program..."
                ], 404);
             }
        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function applicantSend(Request $request, Program $program)
    {
         if ($request->user()->tokenCan('Applicant')) {

            $validator = Validator::make($request->all(), [
                'msg' => 'nullable',
                'file' => 'nullable|max:9000',
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
                $request->file("file")->storeAs("public/messageFiles", $fileNameToStore);

                $urlFile = url('/storage/messageFiles/'.$fileNameToStore);
            }else{
                $urlFile = '';
            }
            $msg = Message::create([
                'program_id'=>$program->id,
                'applicant_id'=>$request->user()->id,
                'from'=>$request->user()->id,
                'to'=>'Admin',
                'msg'=>$request->msg,
                'type'=>'programMessage',
                'status'=>'0',
                'file'=>$urlFile,
            ]);

            return response()->json([
                'status' => true,
                'message' => "Message sent successful.",
                'data' => [
                    'message' => $msg,
                ],
            ]);

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function applicantReadMsg(Request $request, Program $program)
    {
        if ($request->user()->tokenCan('Applicant')) {
            
            $msg = Message::where(['applicant_id'=>$request->user()->id, 'program_id'=>$program->id, 'from'=>'Admin', 'to'=>$request->user()->id])->update([
                'status'=>1
            ]);

            if ($msg) {
                return response()->json([
                    'status' => true,
                    'message' => "All messages from admin are read.",
                    'data' => [
                        'message' => Message::where(['applicant_id'=>$request->user()->id, 'program_id'=>$program->id, 'from'=>'Admin', 'to'=>$request->user()->id])->get(),
                    ],
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "No message found for this user."
                ], 422);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
        
    }

    public function applicantGetUnreadMsg(Request $request, Program $program)
    {
        if ($request->user()->tokenCan('Applicant')) {
            
            $msg = Message::where(['applicant_id'=>$request->user()->id, 'program_id'=>$program->id, 'from'=>'Admin', 'status'=>'0'])->get();

            return response()->json([
                'status' => true,
                'data' => [
                    'unRead' => count($msg),
                ],
            ]);

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 404);
        }
    }

    public function adminGetUnreadMsg(Request $request, Program $program)
    {
        if ($request->user()->tokenCan('Admin')) {
            
            $msg = Message::where(['program_id'=>$program->id, 'to'=>'Admin', 'status'=>'0'])->get();

            return response()->json([
                'status' => true,
                'data' => [
                    'unRead' => count($msg),
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
