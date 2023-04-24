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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

# Admins
Route::prefix('admin')->group(function () {
    # register
    Route::post('register', [\App\Http\Controllers\UserController::class, 'register']);
    
    # verify
    Route::post('verify', [\App\Http\Controllers\UserController::class, 'verify']);
    
    # login
    Route::post('login', [\App\Http\Controllers\UserController::class, 'login']);
    
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
        
        # get
        Route::get('info', [\App\Http\Controllers\ProgramController::class, 'showAll']);
    
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
});





# Applicants
Route::prefix('applicant')->group(function () {
    # register
    Route::post('register', [\App\Http\Controllers\ApplicantController::class, 'register']);
    
    # verify
    Route::post('verify', [\App\Http\Controllers\ApplicantController::class, 'verify']);
    
    # login
    Route::post('login', [\App\Http\Controllers\ApplicantController::class, 'login']);
    
    # recover
    Route::post('recover', [\App\Http\Controllers\ApplicantController::class, 'recover']);
    
    # reset
    Route::post('reset', [\App\Http\Controllers\ApplicantController::class, 'reset']);
    
    Route::middleware('auth:sanctum')->get('profile', [\App\Http\Controllers\ApplicantController::class, 'user']);
});
