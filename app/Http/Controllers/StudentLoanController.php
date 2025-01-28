<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\LoanPaymentPlan;
use App\Models\StudentLoan;
use App\Models\PayrollEmployee;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\Payroll_Interface;


class StudentLoanController extends Controller
{
    private $Payroll_Repository;

    public function __construct(Payroll_Interface $Payroll_Repository)
    {
        $this->Payroll_Repository = $Payroll_Repository;
    }

    public function getLoanPaymentPlan(){
        $plans=LoanPaymentPlan::get();
        return response()->json($plans,200);
    }

    public function getStudentPaymentPlan($id){
        $plans=StudentLoan::where('employee_id',$id)->get();
        return response()->json($plans,200);
    }

    public function saveStudentPaymentPlan(Request $request,$id){

        foreach($request->studentPlans as $plan){

            $studentPlan=StudentLoan::where('employee_id',$id)->where('loan_payment_plan_id',$plan['plan_id'])->first();
            if($studentPlan && $plan['status']==false){
                $studentPlan->delete();
                $employeePayroll=PayrollEmployee::where('employee_id',$id)->where('status','active')->first();
                if($employeePayroll && $plan['type']=='pg_loan'){
                    $employeePayroll->net_pay=$employeePayroll->net_pay + $employeePayroll->pg_loan;
                    $employeePayroll->pg_loan=null;
                    $employeePayroll->save();
                }else if($employeePayroll && $plan['type']=='student_loan'){
                    $employeePayroll->net_pay=$employeePayroll->net_pay + $employeePayroll->student_loan;
                    $employeePayroll->student_loan=null;
                    $employeePayroll->save();
                }
            }else if($studentPlan && $plan['status']==true){
            }
            else{
            $studentPlan=new StudentLoan();
            $studentPlan->employee_id=$id;
            $studentPlan->loan_payment_plan_id=$plan['plan_id'];
            $studentPlan->save();
            }
        }

        $employeePayroll=PayrollEmployee::with('paySchedule')->where('employee_id',$id)->where('status','active')->first();
        
        if($employeePayroll){
        }else{
            $response=['Plan Successfully saved'];
            return response()->json($response,200);
        }

        if($employeePayroll->paySchedule->pay_frequency=='Monthly'){
            $this->Payroll_Repository->set_loan_repayments($employeePayroll->employee_id,'monthly', $employeePayroll);
        }elseif($employeePayroll->paySchedule->pay_frequency=='Weekly'){
            $this->Payroll_Repository->set_loan_repayments($employeePayroll->employee_id,'weekly', $employeePayroll);
        }elseif($employeePayroll->paySchedule->pay_frequency=='Four Weekly'){
            $this->Payroll_Repository->set_loan_repayments($employeePayroll->employee_id,'fourweekly', $employeePayroll);
        }elseif($employeePayroll->paySchedule->pay_frequency=='Forthnightly'){
            $this->Payroll_Repository->set_loan_repayments($employeePayroll->employee_id,'fortnightly', $employeePayroll);
        }
        $employeePayroll->save();

        $response=['Plan Successfully saved'];
        return response()->json($response,200);
    }
}
