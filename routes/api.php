<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HMRCRealTimeInformationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\AllowanceController;
use App\Http\Controllers\PayItemController;
use App\Http\Controllers\PayScheduleController;
use App\Http\Controllers\StatutorypayController;
use App\Http\Controllers\SalaryTypeController;


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

    //---------Company------------
    Route::post('/create-company', [CompanyController::class, 'createCompany']);
    Route::get('/get-step', [CompanyController::class, 'getStep']);
    Route::get('/get-company-details', [CompanyController::class, 'getCompanyDetails']);
    Route::post('/update-company-details', [CompanyController::class, 'updateDetails']);

    //---------Allowance------------
    Route::post('/employment-allowance', [AllowanceController::class, 'enableEmploymentAllowance']);
    Route::post('/apprenticeship-levy', [AllowanceController::class, 'enableApprenticeshipLevy']);
    Route::get('/get-allowances', [AllowanceController::class, 'getAllowances']);

    //---------Pay Items------------
    Route::post('/save-pay-item', [PayItemController::class, 'savePayItem']);
    Route::post('/update-pay-item/{id}', [PayItemController::class, 'updatePayItem']);
    Route::get('/edit-pay-item/{id?}', [PayItemController::class, 'editPayItem']);
    Route::get('/get-pay-items', [PayItemController::class, 'getPayItems']);

    //---------Pay Schedule---------
    Route::post('/save-pay-schedule', [PayScheduleController::class, 'save']);
    Route::get('/get-pay-schedule', [PayScheduleController::class, 'index']);
    Route::get('/edit-pay-schedule/{id?}', [PayScheduleController::class, 'edit']);
    Route::post('/update-pay-schedule/{id?}', [PayScheduleController::class, 'update']);
    Route::get('/delete-pay-schedule/{id?}', [PayScheduleController::class, 'delete']);
    Route::post('/status-pay-schedule/{id?}', [PayScheduleController::class, 'changeStatus']);

    //---------Statutory Pay-----------
    Route::post('/save-statutory-pay', [StatutorypayController::class, 'save']);
    Route::get('/get-statutory-pay', [StatutorypayController::class, 'getStatutoryPay']);

    //---------Salary Type-----------
    Route::post('/save-salary-type', [SalaryTypeController::class, 'save']);
    Route::get('/get-salary-type', [SalaryTypeController::class, 'index']);
    Route::get('/remove-salary-type/{id?}', [SalaryTypeController::class, 'remove']);

});