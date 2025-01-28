<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeSickLeave;
use App\Models\PayrollEmployee;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Repositories\Interfaces\Payroll_Interface;

class EmployeeSickLeaveController extends Controller
{
    private $Payroll_Repository;

    public function __construct(Payroll_Interface $Payroll_Repository)
    {
        $this->Payroll_Repository = $Payroll_Repository;
    }
    
    public function index($id){
        $sickLeave= EmployeeSickLeave::where('employee_id',$id)->get();
        return response()->json($sickLeave,200);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'start_date' => 'required|date',  
            'end_date' => 'nullable|date',  
            'average_weekly_earnings' => 'required|numeric',  
            'days_unavailable' => 'required|numeric',  
            'statutory_eligibility' => 'nullable',          
            'statutory_payable_days' => 'nullable|numeric',
        ]);

        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }

        $sickLeave=new EmployeeSickLeave();
        $sickLeave->employee_id=$request->employee_id;

        $sickLeave->start_date=$request->start_date;
        $sickLeave->end_date=$request->end_date;
        $sickLeave->average_weekly_earnings=$request->average_weekly_earnings;
        $sickLeave->days_unavailable=$request->days_unavailable;
        $sickLeave->statutory_eligibility=$request->statutory_eligibility;

        $waiting_days=$request->qualifying_days ? 3 : 0 ;
        $sickLeave->statutory_waiting_days=$waiting_days;
        $sickLeave->statutory_payable_days=$request->qualifying_days ? ($request->qualifying_days - $waiting_days):0;
        $sickLeave->save();
        $this->Payroll_Repository->update_sickleave_calculations($request->employee_id);

        $response=['Successfully Saved'];
        return response()->json($response,200);
    }

    public function edit($id){
        $sickLeave= EmployeeSickLeave::find($id);
        return response()->json($sickLeave,200);
    }

    public function update(Request $request,$id){
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'start_date' => 'required|date',  
            'end_date' => 'nullable|date',  
            'average_weekly_earnings' => 'required|numeric',  
            'days_unavailable' => 'required|numeric',  
            'statutory_eligibility' => 'nullable',          
            'statutory_payable_days' => 'nullable|numeric',
        ]);

        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }

        $sickLeave=EmployeeSickLeave::find($id);
        $sickLeave->employee_id=$request->employee_id;
        $sickLeave->start_date=$request->start_date;
        $sickLeave->end_date=$request->end_date;
        $sickLeave->average_weekly_earnings=$request->average_weekly_earnings;
        $sickLeave->days_unavailable=$request->days_unavailable;
        $sickLeave->statutory_eligibility=$request->statutory_eligibility;
        $waiting_days=$request->qualifying_days ? 3 : 0 ;
        $sickLeave->statutory_waiting_days=$waiting_days;
        $sickLeave->statutory_payable_days=$request->qualifying_days ? ($request->qualifying_days - $waiting_days):0;
        $sickLeave->save();
        $this->Payroll_Repository->update_sickleave_calculations($request->employee_id);
        $response=['Successfully Updated'];
        return response()->json($response,200);
    }


    public function delete($id){
        $sickLeave= EmployeeSickLeave::find($id);
        if($sickLeave){
            $payroll=PayrollEmployee::with('paySchedule','payroll')->where('employee_id',$sickLeave->employee_id)
            ->where('status','active')->first();
            $payroll->sick_pay=$payroll->sick_pay ? ($payroll->sick_pay - ($sickLeave->statutory_payable_days * 23.35)) : 0;
            // $payroll->gross_pay=$payroll->gross_pay - ($sickLeave->statutory_payable_days * 23.35);

            $workingDays = $this->Payroll_Repository->countWorkingDays($payroll->payroll->pay_run_start_date, $payroll->payroll->pay_run_end_date); //count working days
            
            $per_day_salary=$payroll->base_pay/$workingDays;
            $payroll->gross_pay=$payroll->gross_pay + ($per_day_salary * ($sickLeave->statutory_payable_days + $sickLeave->statutory_payable_days));
            $payroll->save();
            $sickLeave->delete();
            $this->Payroll_Repository->update_sickleave_calculations($sickLeave->employee_id);
            $response=['Successfully deleted'];
            return response()->json($response,200);
        }else{
            $response=['Not Found'];
            return response()->json($response,404);
        } 

    }
}
