<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\LoanPaymentPlan;
use App\Models\StudentLoan;
use Illuminate\Http\Request;


class StudentLoanController extends Controller
{
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
            }else if($studentPlan && $plan['status']==true){
            }
            else{
            $studentPlan=new StudentLoan();
            $studentPlan->employee_id=$id;
            $studentPlan->loan_payment_plan_id=$plan['plan_id'];
            $studentPlan->save();
            }
        }
        
        $response=['Plan Successfully saved'];
        return response()->json($response,200);
    }
}
