<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeBankDetails;
use Illuminate\Http\Request;

class EmployeeBankDetailsController extends Controller
{
    public function getBankDetails($id){
        $bankDetails= EmployeeBankDetails::where('employee_id',$id)->first();
        return response()->json($bankDetails,200);
    }

    public function updateBankDetails(Request $request){
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'bank_name' => 'required',  
            'sort_code' => 'nullable',  
            'account_number' => 'required',
        ]);

        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }

        $bankDetails=EmployeeBankDetails::where('employee_id',$request->employee_id)->first();
        if($bankDetails){

        }else{
            $bankDetails=new EmployeeBankDetails();
        }
        
        $bankDetails->employee_id=$request->employee_id;
        $bankDetails->bank_name=$request->bank_name;
        $bankDetails->sort_code=$request->sort_code;
        $bankDetails->account_number=$request->account_number;
        $bankDetails->save();
        $response=['Successfully Saved'];
        return response()->json($response,200);
    }
}
