<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeSickLeave;
use Illuminate\Http\Request;

class EmployeeSickLeaveController extends Controller
{
    
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
        $sickLeave->statutory_payable_days=$request->statutory_payable_days;
        $sickLeave->save();
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
        $sickLeave->statutory_payable_days=$request->statutory_payable_days;
        $sickLeave->save();
        $response=['Successfully Updated'];
        return response()->json($response,200);
    }


    public function delete($id){
        $sickLeave= EmployeeSickLeave::find($id);
        if($sickLeave){
            $sickLeave->delete();
            $response=['Successfully deleted'];
            return response()->json($response,200);
        }else{
            $response=['Not Found'];
            return response()->json($response,404);
        } 

    }
}
