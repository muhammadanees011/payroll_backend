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
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeYearToDatesController;
use App\Http\Controllers\EmployeeSickLeaveController;
use App\Http\Controllers\EmployeeBankDetailsController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\StudentLoanController;


Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class,'login']);

//-------------Email Verification-------------
Route::post('/verify_email', [AuthController::class, 'verify_email']);

//------------Forgot Password---------------------
Route::post('/forgot_password', [AuthController::class, 'send_forgot_password_otp']);
Route::post('/forgot_password_verify_otp', [AuthController::class, 'forgot_password_verify_otp']);
Route::post('/set_new_password', [AuthController::class, 'set_new_password']);

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/logout', [AuthController::class,'logout']);
    Route::get('/example-paye', [HMRCRealTimeInformationController::class, 'examplePaye']);

    //-------------COMPANY------------
    Route::post('/create-company', [CompanyController::class, 'createCompany']);
    Route::get('/get-step', [CompanyController::class, 'getStep']);
    Route::get('/get-company-details', [CompanyController::class, 'getCompanyDetails']);
    Route::post('/update-company-details', [CompanyController::class, 'updateDetails']);

    //-------------ALLOWANCE------------
    Route::post('/employment-allowance', [AllowanceController::class, 'enableEmploymentAllowance']);
    Route::post('/apprenticeship-levy', [AllowanceController::class, 'enableApprenticeshipLevy']);
    Route::get('/get-allowances', [AllowanceController::class, 'getAllowances']);

    //-------------PAY ITEMS------------
    Route::post('/save-pay-item', [PayItemController::class, 'savePayItem']);
    Route::post('/update-pay-item/{id}', [PayItemController::class, 'updatePayItem']);
    Route::get('/edit-pay-item/{id?}', [PayItemController::class, 'editPayItem']);
    Route::get('/get-pay-items', [PayItemController::class, 'getPayItems']);
    Route::get('/getPayItemsDropDown', [PayItemController::class, 'getPayItemsDropDown']);
    //-------------PAY SCHEDULE---------
    Route::post('/save-pay-schedule', [PayScheduleController::class, 'save']);
    Route::get('/get-pay-schedule', [PayScheduleController::class, 'index']);
    Route::get('/get-pay-schedule-dropdown', [PayScheduleController::class, 'getPayScheduleDropdown']);
    Route::get('/edit-pay-schedule/{id?}', [PayScheduleController::class, 'edit']);
    Route::post('/update-pay-schedule/{id?}', [PayScheduleController::class, 'update']);
    Route::get('/delete-pay-schedule/{id?}', [PayScheduleController::class, 'delete']);
    Route::post('/status-pay-schedule/{id?}', [PayScheduleController::class, 'changeStatus']);

    //---------------STATUTORY PAY-----------
    Route::post('/save-statutory-pay', [StatutorypayController::class, 'save']);
    Route::get('/get-statutory-pay', [StatutorypayController::class, 'getStatutoryPay']);

    //---------------SALARY TYPE------------
    Route::post('/save-salary-type', [SalaryTypeController::class, 'save']);
    Route::get('/get-salary-type', [SalaryTypeController::class, 'index']);
    Route::get('/remove-salary-type/{id?}', [SalaryTypeController::class, 'remove']);

    //---------------EMPLOYEE--------------
    Route::get('/get-employees', [EmployeeController::class, 'getEmployees']);
    Route::post('/create-employee/{id?}', [EmployeeController::class, 'createEmployee']);
    Route::get('/edit-employee/{id?}', [EmployeeController::class, 'editEmployee']);
    Route::post('/update-employee/{id?}', [EmployeeController::class, 'updateEmployee']);
    Route::get('/delete-employee/{id?}', [EmployeeController::class, 'deleteEmployee']);
    Route::post('/search-employees', [EmployeeController::class, 'searchEmployee']);

    Route::get('/getEmployeePaySchedule/{id?}', [EmployeeController::class, 'getEmployeePaySchedule']);
    Route::post('/updateEmployeePaySchedule', [EmployeeController::class, 'updateEmployeePaySchedule']);
    Route::post('/updateEmployeeSalary/{id?}', [EmployeeController::class, 'updateEmployeeSalary']);

    Route::get('/getEmployeeTaxes/{id?}', [EmployeeController::class, 'getEmployeeTaxes']);
    Route::post('/updateEmployeeTaxes/{id?}', [EmployeeController::class, 'updateEmployeeTaxes']);

    Route::get('/getEmployeePension/{id?}', [EmployeeController::class, 'getEmployeePension']);
    Route::post('/updateEmployeePension/{id?}', [EmployeeController::class, 'updateEmployeePension']);

    Route::get('/getPaternityLeave/{id?}', [EmployeeController::class, 'getPaternityLeave']);
    Route::post('/updatePaternityLeave/{id?}', [EmployeeController::class, 'updatePaternityLeave']);

    Route::get('/getEmployeeYTD/{id?}', [EmployeeYearToDatesController::class, 'getEmployeeYTD']);
    Route::post('/storeYTD', [EmployeeYearToDatesController::class, 'storeYTD']);

    Route::get('/getSickLeaves/{id?}', [EmployeeSickLeaveController::class, 'index']);
    Route::post('/storeSickLeave', [EmployeeSickLeaveController::class, 'store']);
    Route::get('/editSickLeave/{id?}', [EmployeeSickLeaveController::class, 'edit']);
    Route::post('/updateSickLeave/{id?}', [EmployeeSickLeaveController::class, 'update']);
    Route::get('/deleteSickLeave/{id?}', [EmployeeSickLeaveController::class, 'delete']);

    Route::get('/getBankDetails/{id?}', [EmployeeBankDetailsController::class, 'getBankDetails']);
    Route::post('/updateBankDetails', [EmployeeBankDetailsController::class, 'updateBankDetails']);

    Route::get('/getLoanPaymentPlan', [StudentLoanController::class, 'getLoanPaymentPlan']);
    Route::get('/getStudentPaymentPlan/{id?}', [StudentLoanController::class, 'getStudentPaymentPlan']);
    Route::post('/saveStudentPaymentPlan/{id?}', [StudentLoanController::class, 'saveStudentPaymentPlan']);
    
    Route::get('/getHMRCSettings', [CompanyController::class, 'getHMRCSettings']);
    Route::post('/updateHMRCSettings', [CompanyController::class, 'updateHMRCSettings']);

    Route::get('/activePayroll', [PayrollController::class, 'activePayroll']);
    Route::get('/salariedEmployees/{id?}', [PayrollController::class, 'salariedEmployees']);
    Route::get('/inputHours/{id?}', [PayrollController::class, 'inputHours']);
    Route::post('/updateInputHours', [PayrollController::class, 'updateInputHours']);
    Route::get('/hourlyEmployees/{id?}', [PayrollController::class, 'hourlyEmployees']);
    Route::post('/runPayroll', [PayrollController::class, 'runPayroll']);
    Route::post('/employeePaySummary', [PayrollController::class, 'employeePaySummary']);
    Route::post('/addPayItemToPaySummary', [PayrollController::class, 'addPayItemToPaySummary']);
    Route::post('/updatePayItemToPaySummary', [PayrollController::class, 'updatePayItemToPaySummary']);
    Route::post('/removePayItemToPaySummary', [PayrollController::class, 'removePayItemToPaySummary']);
});