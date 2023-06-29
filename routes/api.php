<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/user', function (Request $request) {
    // return $request->user();
    return view('mail.MessageNotification');
});

# Admins
Route::prefix('admin')->group(function () {
    # register
    Route::post('register', [\App\Http\Controllers\UserController::class, 'register']);
    
    # verify
    Route::post('verify', [\App\Http\Controllers\UserController::class, 'verify']);
    
    # login
    Route::post('login', [\App\Http\Controllers\UserController::class, 'login']);

    # logout
    Route::middleware('auth:sanctum')->post('logout', [\App\Http\Controllers\UserController::class, 'logout']);
    
    # recover
    Route::post('recover', [\App\Http\Controllers\UserController::class, 'recover']);
    
    # reset
    Route::post('reset', [\App\Http\Controllers\UserController::class, 'reset']);
    
    #get Admin profile
    Route::middleware('auth:sanctum')->get('profile', [\App\Http\Controllers\UserController::class, 'user']);

    // programs
    Route::middleware('auth:sanctum')->prefix('program')->group(function () {
        # store
        Route::post('create', [\App\Http\Controllers\ProgramController::class, 'create']);
        
        # upload
        Route::post('file/upload', [\App\Http\Controllers\ProgramController::class, 'upload']);
        
        # get
        Route::get('list', [\App\Http\Controllers\ProgramController::class, 'showAll']);

        # get
        Route::get('info', [\App\Http\Controllers\ProgramController::class, 'show']);
        Route::get('info/v2', [\App\Http\Controllers\ProgramController::class, 'showObj']);

        // Applications
        Route::middleware('auth:sanctum')->prefix('applications')->group(function () { 
            #Get application
            Route::get('getAll', [\App\Http\Controllers\ProgramController::class, 'getApplications']);
        });
    
    });

    // Region
    Route::middleware('auth:sanctum')->prefix('regions')->group(function () {
        # get
        Route::get('', [\App\Http\Controllers\RegionController::class, 'showAll']);
    
    });

    // Category
    Route::middleware('auth:sanctum')->prefix('category')->group(function () {
        # store
        Route::post('create', [\App\Http\Controllers\CategoryController::class, 'create']);
        
        # get
        Route::get('list', [\App\Http\Controllers\CategoryController::class, 'showAll']);
    
    });

    // Messages
    Route::middleware('auth:sanctum')->prefix('messages')->group(function () {
        # get
        Route::get('{program}', [\App\Http\Controllers\MessageController::class, 'getAll']);

        # send
        Route::post('{program}', [\App\Http\Controllers\MessageController::class, 'adminSend']);

        #update status to read
        Route::post('read/{program}/{applicant}', [\App\Http\Controllers\MessageController::class, 'adminReadMsg']);

        Route::get('get-unread/{program}', [\App\Http\Controllers\MessageController::class, 'adminGetUnreadMsg']);
    });

});





# Applicants
Route::prefix('applicant')->group(function () {
    # register
    Route::post('register', [\App\Http\Controllers\ApplicantController::class, 'register']);
    
    # verify
    Route::post('verify', [\App\Http\Controllers\ApplicantController::class, 'verify']);
    
    # login
    Route::post('login', [\App\Http\Controllers\ApplicantController::class, 'login']);

    # logout
    Route::middleware('auth:sanctum')->post('logout', [\App\Http\Controllers\ApplicantController::class, 'logout']);
    
    # recover
    Route::post('recover', [\App\Http\Controllers\ApplicantController::class, 'recover']);
    
    # reset
    Route::middleware('auth:sanctum')->post('reset', [\App\Http\Controllers\ApplicantController::class, 'reset']);
    
    Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
        Route::get('', [\App\Http\Controllers\ApplicantController::class, 'user']);

        Route::post('add/jv', [\App\Http\Controllers\ApplicantController::class, 'addJv']);

        Route::post('update/jv/{id}', [\App\Http\Controllers\ApplicantController::class, 'updateJv']);

        Route::post('update', [\App\Http\Controllers\ApplicantController::class, 'updateProfile']);
    });


     // programs
    Route::middleware('auth:sanctum')->prefix('program')->group(function () {
    
        # get
        Route::get('list', [\App\Http\Controllers\ProgramController::class, 'showAll']);

        # get
        Route::get('info', [\App\Http\Controllers\ProgramController::class, 'show']);
        Route::get('info/v2', [\App\Http\Controllers\ProgramController::class, 'showObj']);
    
    });

    // Region
    Route::middleware('auth:sanctum')->prefix('regions')->group(function () {
        # get
        Route::get('', [\App\Http\Controllers\RegionController::class, 'showAll']);
    
    });

    // Category
    Route::middleware('auth:sanctum')->prefix('category')->group(function () {
        # store
        Route::post('create', [\App\Http\Controllers\CategoryController::class, 'create']);
        
        # get
        Route::get('list', [\App\Http\Controllers\CategoryController::class, 'showAll']);
    });

    // Application
    Route::middleware('auth:sanctum')->prefix('application')->group(function () {
        # initial
        Route::post('create/initial', [\App\Http\Controllers\ApplicationController::class, 'createInitial']);
        
        # add profile
        Route::post('create/profile', [\App\Http\Controllers\ApplicationController::class, 'createProfile']);
        Route::post('update/profile', [\App\Http\Controllers\ApplicationController::class, 'createProfileUpdate']);
        Route::post('create/profile/upload', [\App\Http\Controllers\ApplicationController::class, 'uploadProfile']);

        # add Staff
        Route::post('create/staff', [\App\Http\Controllers\ApplicationController::class, 'createStaff']);
        Route::post('create/staff/upload', [\App\Http\Controllers\ApplicationController::class, 'uploadStaff']);

        # add Reference Projects
        Route::post('create/projects', [\App\Http\Controllers\ApplicationController::class, 'createProject']);
        Route::post('create/projects/upload', [\App\Http\Controllers\ApplicationController::class, 'uploadProject']);

        # add Financial Info
        Route::post('create/financial', [\App\Http\Controllers\ApplicationController::class, 'createFinancial']);
        Route::post('create/financial/upload', [\App\Http\Controllers\ApplicationController::class, 'uploadFinancial']);

        # add Reference Projects
        Route::post('create/documents', [\App\Http\Controllers\ApplicationController::class, 'createDocument']);
        Route::post('create/documents/upload', [\App\Http\Controllers\ApplicationController::class, 'uploadDocument']);

        # submit
        Route::post('submit', [\App\Http\Controllers\ApplicationController::class, 'submit']);

        Route::post('accept/pre-qualification', [\App\Http\Controllers\ApplicationController::class, 'pre_qualification']);

        #Get application
        Route::get('get', [\App\Http\Controllers\ApplicationController::class, 'getApplication']);

        #Get application progress
        Route::get('get-progress', [\App\Http\Controllers\ApplicationController::class, 'getApplicationProgress']);
    });

    // Messages
    Route::middleware('auth:sanctum')->prefix('messages')->group(function () {
        # get
        Route::get('{program}', [\App\Http\Controllers\MessageController::class, 'applicantGetAll']);

        # send
        Route::post('{program}', [\App\Http\Controllers\MessageController::class, 'applicantSend']);
        
        #update read status
        Route::post('read/{program}', [\App\Http\Controllers\MessageController::class, 'applicantReadMsg']);
        
        # gt unread
        Route::get('get-unread/{program}', [\App\Http\Controllers\MessageController::class, 'applicantGetUnreadMsg']);
    });
});
