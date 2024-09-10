<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HMRCRealTimeInformationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;


Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class,'login']);

//-------------Email Verification-------------
Route::post('/verify_email', [AuthController::class, 'verify_email']);

//------------Forgot Password---------------------
Route::post('/forgot_password', [AuthController::class, 'send_forgot_password_otp']);
Route::post('/forgot_password_verify_otp', [AuthController::class, 'forgot_password_verify_otp']);
Route::post('/set_new_password', [AuthController::class, 'set_new_password']);

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/example-paye', [HMRCRealTimeInformationController::class, 'examplePaye']);

    Route::post('/create-company', [CompanyController::class, 'createCompany']);
    Route::get('/get-step', [CompanyController::class, 'getStep']);
    Route::get('/get-company-details', [CompanyController::class, 'getCompanyDetails']);
});