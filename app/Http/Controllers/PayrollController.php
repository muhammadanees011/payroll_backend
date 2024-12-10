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
use App\Http\Resources\PayrollResource;
use App\Http\Resources\SalariedEmployeeResource;
use App\Http\Resources\HourlyEmployeeResource;
use App\Http\Resources\PayrollEmployeesResource;
use App\Http\Resources\EmployeePaySummaryResource;
use App\Services\NICCalculator;

class PayrollController extends Controller
{
    protected $nicCalculator;

    public function __construct(NICCalculator $nicCalculator)
    {
        $this->nicCalculator = $nicCalculator;
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
            Payroll::with('payschedule')->orderBy('created_at', 'desc')->get()
        );
        return response()->json($payrolls,200);
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
            ->where('payroll_id', $payroll_id)->first();

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
                    $income_tax = $this->nicCalculator->calculatePAYE($payroll->gross_pay, $employeeData['pay_frequency'], '1257L');
                    $payroll->paye_income_tax=$income_tax;
                    $payroll->net_pay=($payroll->gross_pay)-($payroll->paye_income_tax + $payroll->employee_pension + $payroll->employee_nic);
                } elseif ($type == 'Salaried') {
                    if($employeeData['pay_frequency']=='Monthly'){
                        $payroll->gross_pay = $employeeData['monthly_salary'];
                        $payroll->net_pay = $employeeData['monthly_salary'];
                        $payroll->base_pay = $employeeData['monthly_salary'];
                    }else if($employeeData->pay_frequency=='Weekly'){
                        $payroll->gross_pay = $employeeData['weekly_salary'];
                        $payroll->net_pay = $employeeData['weekly_salary'];
                        $payroll->base_pay = $employeeData['weekly_salary'];
                    }
                    $payroll->salary_type = 'Salaried';
                    $nic_data=$this->calculateNIC($payroll->gross_pay);
                    $payroll->employee_nic=$nic_data['employee_nic'];
                    $payroll->employer_nic=$nic_data['employer_nic'];
                    $payroll->employee_pension=($payroll->gross_pay) * ($employeePension->employee_contribution / 100);
                    $payroll->employer_pension=($payroll->gross_pay) * ($employeePension->employer_contribution / 100);
                    $income_tax = $this->nicCalculator->calculatePAYE($payroll->gross_pay, $employeeData['pay_frequency'], '1257L');
                    $payroll->paye_income_tax=$income_tax;
                    $payroll->net_pay=($payroll->gross_pay)-($payroll->paye_income_tax + $payroll->employee_pension + $payroll->employee_nic);
                }
        
                $payroll->save();
            }
        }
        $response['message']='Success';
        return response()->json($response,200);
        
    }

    public function salariedEmployees($id){
        $salariedEmployees = PayrollEmployeesResource::collection(
            PayrollEmployee::with('employee','employementdetail','payroll','payschedule')
            ->where('pay_schedule_id',$id)
            ->where('salary_type','Salaried')
            ->orderBy('created_at', 'desc')->get()
        );
        return response()->json($salariedEmployees,200);
    }

    public function hourlyEmployees($id){
        $hourlyEmployees = PayrollEmployeesResource::collection(
            PayrollEmployee::with('employee','employementdetail','payroll','payschedule')
            ->where('pay_schedule_id',$id)
            ->where('salary_type','Hourly')
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
            $payroll = PayrollEmployee::where('employee_id', $employeeData['employee_id'])
                ->where('payroll_id', $employeeData['payroll_id'])
                ->first();

            if ($payroll) {
                $payroll->hours_worked = $employeeData['hours_worked'];
                $payroll->gross_pay = $employeeData['hours_worked'] * $employeeData['hourly_equivalent'];
                $payroll->net_pay = $employeeData['hours_worked'] * $employeeData['hourly_equivalent'];
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
        $payroll->employee_pension=($payroll->gross_pay) * ($employeePension->employee_contribution / 100);
        $payroll->employer_pension=($payroll->gross_pay) * ($employeePension->employer_contribution / 100);
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
        $payroll->employee_pension=($payroll->gross_pay) * ($employeePension->employee_contribution / 100);
        $payroll->employer_pension=($payroll->gross_pay) * ($employeePension->employer_contribution / 100);
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
        $payroll->employee_pension=($payroll->gross_pay) * ($employeePension->employee_contribution / 100);
        $payroll->employer_pension=($payroll->gross_pay) * ($employeePension->employer_contribution / 100);
        $income_tax = $this->nicCalculator->calculatePAYE($payroll->gross_pay, 'Monthly', '1257L');
        $payroll->paye_income_tax=$income_tax;
        $payroll->net_pay=($payroll->gross_pay)-($payroll->paye_income_tax + $payroll->employee_pension + $payroll->employee_nic);
        $payroll->save();
        return response()->json(['message' => 'Successfully Deleted'], 200);
    }
}
