<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Payroll;
use App\Models\EmployementDetail;
use App\Models\PayrollEmployee;
use App\Models\EmployeePension;
use App\Models\EmployeePayItem;
use App\Models\EmployeePayslip;
use App\Models\Employee;
use App\Models\Company;
use App\Http\Resources\PayrollResource;
use App\Http\Resources\SalariedEmployeeResource;
use App\Http\Resources\HourlyEmployeeResource;
use App\Http\Resources\PayrollEmployeesResource;
use App\Http\Resources\EmployeePaySummaryResource;
use App\Http\Resources\EmployeePayslipResource;
use App\Http\Resources\PayrollReviewResource;
use App\Services\NICCalculator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\PayslipMail;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;
use App\Repositories\Interfaces\Payroll_Interface;

class PayrollController extends Controller
{
    protected $nicCalculator;
    private $Payroll_Repository;

    public function __construct(NICCalculator $nicCalculator, Payroll_Interface $Payroll_Repository)
    {
        $this->nicCalculator = $nicCalculator;
        $this->Payroll_Repository = $Payroll_Repository;
    }

    public function calculateNIC($grossEarnings)
    {
        // $grossEarnings = 5000; // Example gross earnings
        $isApprentice = true; // Example apprentice flag
        $frequency = 'Monthly';

        $nic = $this->nicCalculator->calculateNIC($grossEarnings, $frequency, $isApprentice);

        $nic_calculations=[
            'gross_earnings' => $grossEarnings,
            'employee_nic' => $nic['employee_nic'],
            'employer_nic' => $nic['employer_nic'],
        ];

        return $nic_calculations;
    }
    
    public function activePayroll(){
        $payrolls = PayrollResource::collection(
            Payroll::with('payschedule')->where('status','draft')->orderBy('created_at', 'desc')->get()
        );
        return response()->json($payrolls,200);
    }

    public function historyPayroll(){
        $payrolls = PayrollResource::collection(
            Payroll::with('payschedule')->where('status','history')->orderBy('created_at', 'desc')->get()
        );
        return response()->json($payrolls,200);
    }

    public function getPayrollDetail(Request $request){
        $payroll=Payroll::where('id',$request->payroll_id)->select('tax_period','pay_date')->first();
        return response()->json($payroll,200);
    }

    public function runPayroll(Request $request){
        $validator = Validator::make($request->all(), [
            'payschedule_id' => 'required',
            'payroll_id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $salariedEmployees = SalariedEmployeeResource::collection(
            EmployementDetail::with('employee','payschedule')
            ->where('pay_schedule_id',$request->payschedule_id)
            ->where('salary_type','Salaried')
            ->orderBy('created_at', 'desc')->get()
        );

        $hourlyEmployees = SalariedEmployeeResource::collection(
            EmployementDetail::with('employee','payschedule')
            ->where('pay_schedule_id',$request->payschedule_id)
            ->where('salary_type','Hourly')
            ->orderBy('created_at', 'desc')->get()
        );
        
        $salariedEmployees = $salariedEmployees->toArray($request);
        $hourlyEmployees = $hourlyEmployees->toArray($request);
        $this->addPayrollEmployees($hourlyEmployees, $request->payroll_id,'Hourly');
        $this->addPayrollEmployees($salariedEmployees, $request->payroll_id,'Salaried');
        $response['message']='Success';
        return response()->json($response,200);
    }

    
    public function addPayrollEmployees($employees, $payroll_id, $type)
    {
        foreach ($employees as $employeeData) {
            $payroll = PayrollEmployee::where('employee_id', $employeeData['employee_id'])
            ->where('payroll_id', $payroll_id)->where('status','active')->first();

            $employeePension = EmployeePension::where('employee_id', $employeeData['employee_id'])->first();
        
            if (!$payroll) {
                $payroll = new PayrollEmployee();
                $payroll->payroll_id = $payroll_id;
                $payroll->employee_id = $employeeData['employee_id'];
                $payroll->pay_schedule_id = $employeeData['pay_schedule_id'];
        
                if ($type == 'Hourly') {
                    $payroll->hours_worked = 0;
                    $payroll->hourly_rate = $employeeData['hourly_equivalent'];
                    $payroll->gross_pay = 0;
                    $payroll->base_pay = 0;
                    $payroll->salary_type = 'Hourly';
                    $payroll->employee_nic = 0;
                    $payroll->employer_nic = 0;
                    $payroll->employee_pension = 0;
                    $payroll->employer_pension = 0;
                    $payroll->pg_loan=0;
                    $payroll->student_loan=0;
                    $income_tax = $this->nicCalculator->calculatePAYE($payroll->gross_pay, $employeeData['pay_frequency'], '1257L');
                    $payroll->paye_income_tax=$income_tax;
                    $payroll->net_pay=($payroll->gross_pay)-($payroll->pg_loan + $payroll->student_loan + $payroll->paye_income_tax + $payroll->employee_pension + $payroll->employee_nic);
                    $this->Payroll_Repository->update_payroll_calculations($employeeData['employee_id']);
                    $this->Payroll_Repository->update_paternitypay_calculations($employeeData['employee_id']);
                    // $payroll->net_pay = $payroll->net_pay + ($payroll->sick_pay ? $payroll->sick_pay: 0);
                } elseif ($type == 'Salaried') {
                    if($employeeData['pay_frequency']=='Monthly'){
                        $payroll->gross_pay = $employeeData['monthly_salary'];
                        $payroll->net_pay = $employeeData['monthly_salary'];
                        $payroll->base_pay = $employeeData['monthly_salary'];
                        $this->Payroll_Repository->set_loan_repayments($payroll->employee_id,'monthly', $payroll);
                    }else if($employeeData->pay_frequency=='Weekly'){
                        $payroll->gross_pay = $employeeData['weekly_salary'];
                        $payroll->net_pay = $employeeData['weekly_salary'];
                        $payroll->base_pay = $employeeData['weekly_salary'];
                        $this->Payroll_Repository->set_loan_repayments($payroll->employee_id,'weekly', $payroll);
                    }
                    $payroll->salary_type = 'Salaried';
                    $nic_data=$this->calculateNIC($payroll->gross_pay);
                    $payroll->employee_nic=$nic_data['employee_nic'];
                    $payroll->employer_nic=$nic_data['employer_nic'];
                    $payroll->employee_pension=$employeePension ? ($payroll->gross_pay) * (($employeePension->employee_contribution ?? 0) / 100):0;
                    $payroll->employer_pension=$employeePension ? ($payroll->gross_pay) * (($employeePension->employer_contribution ?? 0) / 100):0;
                    $income_tax = $this->nicCalculator->calculatePAYE($payroll->gross_pay, $employeeData['pay_frequency'], '1257L');
                    $payroll->paye_income_tax=$income_tax;
                    $payroll->net_pay=($payroll->gross_pay)-($payroll->pg_loan + $payroll->student_loan + $payroll->paye_income_tax + $payroll->employee_pension + $payroll->employee_nic);
                    $this->Payroll_Repository->update_payroll_calculations($employeeData['employee_id']);
                    $this->Payroll_Repository->update_paternitypay_calculations($employeeData['employee_id']);
                    // $payroll->net_pay = $payroll->net_pay + ($payroll->sick_pay ? $payroll->sick_pay: 0);
                }
        
                $payroll->save();
            }
        }
        $response['message']='Success';
        return response()->json($response,200);
        
    }

    public function sendPayslipAllEmployees(Request $request){
        $validator = Validator::make($request->all(), [
            'payschedule_id' => 'required',
            'payroll_id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }

        $allEmployees = PayrollEmployee::with('employee')->where('pay_schedule_id',$request->payschedule_id)->get();

        foreach($allEmployees as $employee){

            $paySummary = PayrollEmployee::with('employee', 'payroll', 'employeestarterdetail', 'employementdetail', 'payschedule')
            ->where('employee_id', $employee->employee_id)
            ->where('payroll_id', $request->payroll_id)
            ->first();

            $employeePayItems=EmployeePayItem::with('salarytype', 'payitem')
            ->where('employee_id', $employee->employee_id)
            ->where('payroll_id', $request->payroll_id)
            ->get();

            $company=Company::select('name','address_line_1','address_line_2','post_code','city')->first();

            $yearlyEarnings = PayrollEmployee::where('employee_id', $employee->employee_id)
            ->selectRaw('
                SUM(gross_pay) as gross_earnings_this_year,
                SUM(employee_nic) as employee_nic_this_year,
                SUM(paye_income_tax) as paye_income_tax_this_year,
                SUM(employee_pension) as employee_pension_this_year,
                SUM(employer_nic) as employer_nic_this_year,
                SUM(employer_pension) as employer_pension_this_year
            ')
            ->first();

            if (!$paySummary) {
                return response()->json(['message' => 'Employee not found'], 404);
            }
            $paySummary->setRelation('company', $company);
            $paySummary->setRelation('employeePayItems', $employeePayItems);
            $paySummary->setRelation('yearlyEarnings', $yearlyEarnings);
            $employeePaySlip=new EmployeePayslipResource($paySummary);
            $employeePaySlip = json_decode(json_encode($employeePaySlip), true);
            
            $comapny=Company::first();
            $payroll=Payroll::find($request->payroll_id);
            $data = [
                'employee_name' => $employee->employee->forename.' '.$employee->employee->surname,
                'company_name' => $comapny->name,
                'paydate' => $payroll->pay_date,
            ];
            
            $path = 'payslip_' . $employee->employee->forename . ' ' . $employee->employee->surname .'_'.$employee->employee_id.'_'.$payroll->pay_date.'.pdf';
            // Check if the file already exists
            if (Storage::exists($path)) {
                // Delete the existing file
                Storage::delete($path);
            }
            $pdf = Pdf::loadView('payslip', compact('employeePaySlip'));
            // Save PDF to server
            $pdf->save(storage_path('app/' . $path));
            // Send email with attachment
            Mail::to($employee->employee->work_email)->send(new PayslipMail($path, $data));

            $checkPaySlip=EmployeePayslip::where('employee_id',$employee->employee_id)
            ->where('payroll_id',$request->payroll_id)
            ->where('pay_date',$payroll->pay_date)
            ->delete();

            $payslip = new EmployeePayslip();
            $payslip->employee_id = $employee->employee_id;
            $payslip->payroll_id = $request->payroll_id;
            $payslip->tax_period = $payroll->tax_period;
            $payslip->net_pay = $employeePaySlip['net_pay'];
            $payslip->pay_date = $payroll->pay_date;
            $payslip->file_name = $path;
            $payslip->file_path = 'app/' . $path;
            $payslip->save();
        }
        return response()->json(['message' => 'Payslip sent successfully!']);
    }

    public function getEmployeePayslips($employee_id){
        $paySlips=EmployeePayslip::where('employee_id',$employee_id)->get();
        return response()->json($paySlips,200);
    }

    public function viewEmployeePayslip($id){
        $payslip = EmployeePayslip::findOrFail($id);

        $filePath = storage_path($payslip->file_path);
    
        // Check if file exists
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found.'], 404);
        }
    
        // Return the file as a download response
        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $payslip->file_name . '"'
        ]);
    }

    public function downloadEmployeePayslip($id){
        $payslip = EmployeePayslip::findOrFail($id);

        $filePath = storage_path($payslip->file_path);
    
        // Check if file exists
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found.'], 404);
        }
    
        // Return the file as a download response
        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $payslip->file_name . '"',
        ]);
    }

    public function downloadAllPayslips($employee_id)
    {
        // Create a temporary file for the zip
        $zipFileName = 'payslips.zip';
        $zipFilePath = storage_path('app/' .$zipFileName);

        // Create a new ZipArchive instance
        $zipFilePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $zipFilePath);
        $zip = new ZipArchive;

        $result = $zip->open($zipFilePath, ZipArchive::CREATE); // Open ZIP
        return response()->download($zipFilePath);

        return response()->file($zipFilePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
        ]);

        if ($result === TRUE) {
            $zip->close(); // Close ZIP
            return response()->json(['success' => 'ZIP file created.', 'path' => $zipFilePath]);
        } else {
            return response()->json(['error' => 'Failed to create ZIP. Error code: ' . $result]);
        }
        
        // Open the zip file for writing
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
            
            // $payslips = EmployeePayslip::where('employee_id',$employee_id)->get();
            // foreach ($payslips as $payslip) {
            //     if ($payslip) {
            //         $filePath = storage_path($payslip->file_path);  
            //         if (file_exists($filePath)) {
            //             $zip->addFile($filePath, $payslip->file_name);
            //         }
            //     }
            // }     
            $zip->close();
            // Check if the zip file was created
            if (file_exists($zipFilePath) && filesize($zipFilePath) > 0) {
                // Return the zip file as a response for download
                return response()->download($zipFilePath)->deleteFileAfterSend(true);
            } else {
                return response()->json(['error' => 'No files found to download.'], 404);
            }
        } else {
            return response()->json(['error' => 'Failed to create zip file.'], 500);
        }
    }

    public function deleteEmployeePayslip($id){
        $payslip = EmployeePayslip::findOrFail($id);

        $filePath = storage_path($payslip->file_path);
    
        // Check if file exists
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found.'], 404);
        }
        $payslip->delete();
        unlink($filePath);
        return response()->json(['Success','Payslip deleted successfully'],200);
    }

    public function salariedEmployees(Request $request){
        $validator = Validator::make($request->all(), [
            'payschedule_id' => 'required',
            'payroll_status' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }

        $salariedEmployees = PayrollEmployeesResource::collection(
            PayrollEmployee::with('employee','employementdetail','payroll','payschedule')
            ->where('pay_schedule_id',$request->payschedule_id)
            ->where('salary_type','Salaried')
            ->where('status',$request->payroll_status)
            ->orderBy('created_at', 'desc')->get()
        );
        return response()->json($salariedEmployees,200);
    }

    public function hourlyEmployees(Request $request){
        $validator = Validator::make($request->all(), [
            'payschedule_id' => 'required',
            'payroll_status' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $hourlyEmployees = PayrollEmployeesResource::collection(
            PayrollEmployee::with('employee','employementdetail','payroll','payschedule')
            ->where('pay_schedule_id',$request->payschedule_id)
            ->where('salary_type','Hourly')
            ->where('status',$request->payroll_status)
            ->orderBy('created_at', 'desc')->get()
        );
        return response()->json($hourlyEmployees,200);
    }

    public function inputHours($id){
        $salariedEmployees = SalariedEmployeeResource::collection(
            EmployementDetail::with('employee.payrollEmployee','payschedule')
            ->where('pay_schedule_id',$id)
            ->where('salary_type','Hourly')
            ->orderBy('created_at', 'desc')->get()
        );
        return response()->json($salariedEmployees,200);
    }

    public function updateInputHours(Request $request)
    {
        foreach ($request->input('employees') as $employeeData) {
            $payroll = PayrollEmployee::with('paySchedule')->where('employee_id', $employeeData['employee_id'])
                ->where('payroll_id', $employeeData['payroll_id'])
                ->first();

            if ($payroll) {
                $payroll->hours_worked = $employeeData['hours_worked'];
                $payroll->base_pay  = $employeeData['hours_worked'] * $employeeData['hourly_equivalent'];
                $payroll->gross_pay = $employeeData['hours_worked'] * $employeeData['hourly_equivalent'];
                $payroll->net_pay = $employeeData['hours_worked'] * $employeeData['hourly_equivalent'];
        
                $empPayItemsAmount = EmployeePayItem::where('employee_id',$payroll->employee_id)
                ->where('payroll_id',$payroll->payroll_id)->sum('amount');
        
                $employeePension = EmployeePension::where('employee_id', $payroll->employee_id)->first();
        
                $payroll->gross_pay=$payroll->base_pay + $empPayItemsAmount;
        
                $nic_data=$this->calculateNIC($payroll->gross_pay);
                $payroll->employee_nic=$nic_data['employee_nic'];
                $payroll->employer_nic=$nic_data['employer_nic'];
                $payroll->employee_pension=($payroll->gross_pay) * (($employeePension->employee_contribution ?? 0) / 100);
                $payroll->employer_pension=($payroll->gross_pay) * (($employeePension->employer_contribution ?? 0) / 100);
                $income_tax = $this->nicCalculator->calculatePAYE($payroll->gross_pay, 'Monthly', '1257L');
                if($payroll->paySchedule->pay_frequency=='Monthly'){
                    $this->Payroll_Repository->set_loan_repayments($payroll->employee_id,'monthly', $payroll);
                }elseif($payroll->paySchedule->pay_frequency=='Weekly'){
                    $this->Payroll_Repository->set_loan_repayments($payroll->employee_id,'weekly', $payroll);
                }elseif($payroll->paySchedule->pay_frequency=='Four Weekly'){
                    $this->Payroll_Repository->set_loan_repayments($payroll->employee_id,'fourweekly', $payroll);
                }elseif($payroll->paySchedule->pay_frequency=='Forthnightly'){
                    $this->Payroll_Repository->set_loan_repayments($payroll->employee_id,'forthnightly', $payroll);
                }
                $payroll->paye_income_tax=$income_tax;
                $payroll->net_pay=($payroll->gross_pay)-($payroll->pg_loan + $payroll->student_loan + $payroll->paye_income_tax + $payroll->employee_pension + $payroll->employee_nic);
                $this->Payroll_Repository->update_payroll_calculations($employeeData['employee_id']);
                $this->Payroll_Repository->update_paternitypay_calculations($employeeData['employee_id']);
                $payroll->save();
                
            } else {
                $payroll = new PayrollEmployee();
                $payroll->payroll_id = $employeeData['payroll_id'];
                $payroll->employee_id = $employeeData['employee_id'];
                $payroll->hours_worked = $employeeData['hours_worked'];
                $payroll->hourly_rate = $employeeData['hourly_equivalent'];
                $payroll->gross_pay = $employeeData['hours_worked'] * $employeeData['hourly_equivalent'];
                $payroll->net_pay = $employeeData['hours_worked'] * $employeeData['hourly_equivalent'];
                $payroll->salary_type = 'Hourly';
                $payroll->save();
            }
        }
    }

    public function employeePaySummary(Request $request){
        $paySummary = PayrollEmployee::with('employee', 'employeestarterdetail', 'employementdetail', 'payschedule')
        ->where('employee_id', $request->employee_id)
        ->where('payroll_id', $request->payroll_id)
        ->first();

        $employeePayItems=EmployeePayItem::with('salarytype', 'payitem')
        ->where('employee_id', $request->employee_id)
        ->where('payroll_id', $request->payroll_id)
        ->get();

        if (!$paySummary) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $employeePayItems = EmployeePayItem::with(['salarytype', 'payitem'])
        ->where('employee_id', $request->employee_id)
        ->where('payroll_id', $request->payroll_id)
        ->get();

        $paySummary->setRelation('employeePayItems', $employeePayItems);
        
        return response()->json(new EmployeePaySummaryResource($paySummary), 200);
    }


    public function addPayItemToPaySummary(Request $request){
        $employeePayItem = new EmployeePayItem();
        $employeePayItem->employee_id=$request->employee_id;
        $employeePayItem->payroll_id=$request->payroll_id;

        if($request->type=='payitem'){
            $employeePayItem->pay_item_id=$request->id;
            $employeePayItem->type='PayItem';
        }elseif($request->type=='salary'){
            $employeePayItem->salary_type_id=$request->id;
            $employeePayItem->type='Salary';
        }
        $employeePayItem->units=$request->units;
        $employeePayItem->hours=$request->hours;
        $employeePayItem->salary_rate=$request->salary_rate;
        $employeePayItem->amount=$request->amount;
        $employeePayItem->save();

        $payroll=PayrollEmployee::where('payroll_id',$request->payroll_id)
        ->where('employee_id',$request->employee_id)->first();

        $empPayItemsAmount = EmployeePayItem::where('employee_id',$request->employee_id)
        ->where('payroll_id',$request->payroll_id)->sum('amount');

        $employeePension = EmployeePension::where('employee_id', $request->employee_id)->first();

        $payroll->gross_pay=$payroll->gross_pay + $empPayItemsAmount;

        $nic_data=$this->calculateNIC($payroll->gross_pay);
        $payroll->employee_nic=$nic_data['employee_nic'];
        $payroll->employer_nic=$nic_data['employer_nic'];
        $payroll->employee_pension=($payroll->gross_pay) * (($employeePension->employee_contribution ?? 0) / 100);
        $payroll->employer_pension=($payroll->gross_pay) * (($employeePension->employer_contribution ?? 0) / 100);
        $income_tax = $this->nicCalculator->calculatePAYE($payroll->gross_pay, 'Monthly', '1257L');
        $payroll->paye_income_tax=$income_tax;
        $payroll->net_pay=($payroll->gross_pay)-($payroll->paye_income_tax + $payroll->employee_pension + $payroll->employee_nic);
        $payroll->save();

        return response()->json(['message' => 'Successfully Aded'], 200);
    }

    public function updatePayItemToPaySummary(Request $request){
        $employeePayItem = EmployeePayItem::where('id',$request->id)->first();

        if($request->type=='payitem'){
            $employeePayItem->pay_item_id=$request->item_id;
            $employeePayItem->salary_type_id=null;
            $employeePayItem->type='PayItem';
        }else if($request->type=='salary'){
            $employeePayItem->salary_type_id=$request->item_id;
            $employeePayItem->pay_item_id=null;
            $employeePayItem->type='Salary';
        }
        $employeePayItem->units=$request->units;
        $employeePayItem->hours=$request->hours;
        $employeePayItem->salary_rate=$request->salary_rate;
        $employeePayItem->amount=$request->amount;
        $employeePayItem->save();

        $payroll=PayrollEmployee::where('payroll_id',$employeePayItem->payroll_id)
        ->where('employee_id',$employeePayItem->employee_id)->first();

        $empPayItemsAmount = EmployeePayItem::where('employee_id',$employeePayItem->employee_id)
        ->where('payroll_id',$employeePayItem->payroll_id)->sum('amount');

        $employeePension = EmployeePension::where('employee_id', $employeePayItem->employee_id)->first();

        $payroll->gross_pay=$payroll->gross_pay + $empPayItemsAmount;

        $nic_data=$this->calculateNIC($payroll->gross_pay);
        $payroll->employee_nic=$nic_data['employee_nic'];
        $payroll->employer_nic=$nic_data['employer_nic'];
        $payroll->employee_pension=($payroll->gross_pay) * (($employeePension->employee_contribution ?? 0) / 100);
        $payroll->employer_pension=($payroll->gross_pay) * (($employeePension->employer_contribution ?? 0) / 100);
        $income_tax = $this->nicCalculator->calculatePAYE($payroll->gross_pay, 'Monthly', '1257L');
        $payroll->paye_income_tax=$income_tax;
        $payroll->net_pay=($payroll->gross_pay)-($payroll->paye_income_tax + $payroll->employee_pension + $payroll->employee_nic);
        $payroll->save();
        return response()->json(['message' => 'Successfully Updated'], 200);
    }

    public function removePayItemToPaySummary(Request $request){
        $payItem = EmployeePayItem::where('id',$request->id)->first();
        $employeePayItem= $payItem;
        $payItem->delete();

        $payroll=PayrollEmployee::where('payroll_id',$employeePayItem->payroll_id)
        ->where('employee_id',$employeePayItem->employee_id)->first();

        $empPayItemsAmount = EmployeePayItem::where('employee_id',$employeePayItem->employee_id)
        ->where('payroll_id',$employeePayItem->payroll_id)->sum('amount');

        $employeePension = EmployeePension::where('employee_id', $employeePayItem->employee_id)->first();

        $payroll->gross_pay=$payroll->base_pay + $empPayItemsAmount;

        $nic_data=$this->calculateNIC($payroll->gross_pay);
        $payroll->employee_nic=$nic_data['employee_nic'];
        $payroll->employer_nic=$nic_data['employer_nic'];
        $payroll->employee_pension=($payroll->gross_pay) * (($employeePension->employee_contribution ?? 0) / 100);
        $payroll->employer_pension=($payroll->gross_pay) * (($employeePension->employer_contribution ?? 0) / 100);
        $income_tax = $this->nicCalculator->calculatePAYE($payroll->gross_pay, 'Monthly', '1257L');
        $payroll->paye_income_tax=$income_tax;
        $payroll->net_pay=($payroll->gross_pay)-($payroll->paye_income_tax + $payroll->employee_pension + $payroll->employee_nic);
        $payroll->save();
        return response()->json(['message' => 'Successfully Deleted'], 200);
    }

    public function employeePaySlip(Request $request){
        $paySummary = PayrollEmployee::with('employee', 'payroll', 'employeestarterdetail', 'employementdetail', 'payschedule')
        ->where('employee_id', $request->employee_id)
        ->where('payroll_id', $request->payroll_id)
        ->first();

        $employeePayItems=EmployeePayItem::with('salarytype', 'payitem')
        ->where('employee_id', $request->employee_id)
        ->where('payroll_id', $request->payroll_id)
        ->get();

        $company=Company::select('name','address_line_1','address_line_2','post_code','city')->first();

        $yearlyEarnings = PayrollEmployee::where('employee_id', $request->employee_id)
        ->selectRaw('
            SUM(gross_pay) as gross_earnings_this_year,
            SUM(employee_nic) as employee_nic_this_year,
            SUM(paye_income_tax) as paye_income_tax_this_year,
            SUM(employee_pension) as employee_pension_this_year,
            SUM(employer_nic) as employer_nic_this_year,
            SUM(employer_pension) as employer_pension_this_year
        ')
        ->first();

        if (!$paySummary) {
            return response()->json(['message' => 'Employee not found'], 404);
        }
        $paySummary->setRelation('company', $company);
        $paySummary->setRelation('employeePayItems', $employeePayItems);
        $paySummary->setRelation('yearlyEarnings', $yearlyEarnings);
        
        return response()->json(new EmployeePayslipResource($paySummary), 200);
    }

    public function sendpayslip(Request $request){
        $request->validate([
            'payslip' => 'required|file|mimes:pdf',
            'employee_id' => 'required|integer',
            'payroll_id' => 'required|integer',
        ]);
        $path = $request->file('payslip')->store('payslips');
        $employee=Employee::find($request->employee_id);
        $employee = Employee::find($request->employee_id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }
        $comapny=Company::first();
        $payroll=Payroll::find($request->payroll_id);
        $employeeEmail = $employee->work_email;
        $data = [
            'employee_name' => $employee->forename.' '.$employee->surname,
            'company_name' => $comapny->name,
            'paydate' => $payroll->pay_date,
        ];
        Mail::to($employeeEmail)->send(new PayslipMail($path, $data));
        Storage::delete($path);
        return response()->json(['message' => 'Payslip sent successfully'], 200);
    }


    public function reviewPayroll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payroll_id' => 'required',
            'payroll_status' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $paySummary = PayrollEmployee::where('payroll_id', $request->payroll_id)
        ->where('status',$request->payroll_status)
        ->selectRaw('
            SUM(net_pay) as net_pay,
            SUM(gross_pay) as gross_pay,
            SUM(employee_pension) as employee_pension,
            SUM(employer_pension) as employer_pension,
            SUM(employee_nic) as employee_nic,
            SUM(employer_nic) as employer_nic,
            SUM(base_pay) as base_pay,
            SUM(paye_income_tax) as paye_income_tax,
            SUM(student_loan) as student_loan,
            SUM(pg_loan) as pg_loan,
            COUNT(*) as employees_count
        ')
        ->first();

        $payroll=Payroll::find( $request->payroll_id)->first();

        $grossAdditionSum = EmployeePayItem::where('payroll_id', $request->payroll_id)
        ->whereHas('payitem', function ($query) {
            $query->where('payment_type', 'Gross Addition');
        })
        ->sum('amount');

        $grossDeductionSum = EmployeePayItem::where('payroll_id', $request->payroll_id)
        ->whereHas('payitem', function ($query) {
            $query->where('payment_type', 'Gross Deduction');
        })
        ->sum('amount');

        $netAdditionSum = EmployeePayItem::where('payroll_id', $request->payroll_id)
        ->whereHas('payitem', function ($query) {
            $query->where('payment_type', 'Net Addition');
        })
        ->sum('amount');

        $netDeductionSum = EmployeePayItem::where('payroll_id', $request->payroll_id)
        ->whereHas('payitem', function ($query) {
            $query->where('payment_type', 'Net Deduction');
        })
        ->sum('amount');
        $paySummary->gross_addition_sum = $grossAdditionSum;
        $paySummary->gross_deduction_sum = $grossDeductionSum;
        $paySummary->net_addition_sum = $netAdditionSum;
        $paySummary->net_deduction_sum = $netDeductionSum;
        $paySummary->tax_period = $payroll->tax_period;
        $paySummary->pay_date = $payroll->pay_date;

        if (!$paySummary) {
            return response()->json(['message' => 'Payroll not found'], 404);
        }
        // $paySummary->setRelation('employeePayItems', $employeePayItems);
        return response()->json(new PayrollReviewResource($paySummary), 200);
    }

    public function submitPayroll(Request $request){

        $payroll=Payroll::find($request->payroll_id);
        PayrollEmployee::where('payroll_id',$request->payroll_id)
        ->update(['status' => 'history']);
        $payroll_employees=PayrollEmployee::where('payroll_id',$request->payroll_id)->get();

        if($payroll){
            $payroll->status='history';
            $payroll->save();
        }

        if($payroll_employees){
            foreach($payroll_employees as $employee){
                $payitem=EmployeePayItem::where('employee_id',$employee->employee_id)
                ->where('payroll_id',$request->payroll_id)
                ->where('status','draft')
                ->update(['status' => 'history']);
            }
        }

        return response()->json(['successfully submitted the fps'], 200);
    }

}
