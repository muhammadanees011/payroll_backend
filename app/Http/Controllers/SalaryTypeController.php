<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\SalaryType;

class SalaryTypeController extends Controller
{
    public function index(){
        $salaryTypes = SalaryType::orderBy('created_at', 'desc')->get();
        return response()->json($salaryTypes,200);
    }

    public function save(Request $request){
        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string',
            'pensionable' => 'nullable|array',
            'multiply_by' => 'nullable|numeric',
            'type' => 'required|array',
            'code' => 'nullable',
            'salary_period' => 'nullable|array',
            'salary_rate' => 'nullable|numeric',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $salaryType=new SalaryType();
        $salaryType->code=$request->code;
        $salaryType->description=$request->description;
        $salaryType->type=$request->type['name'];
        $salaryType->salary_period=$request->salary_period ?$request->salary_period['name']:null;
        $salaryType->salary_rate=$request->salary_rate;
        $salaryType->multiply_by=$request->multiply_by;
        $salaryType->pensionable=$request->pensionable[0] ? ($request->pensionable[0]=='pensionable' ? true:false):false;
        $salaryType->save();
        $response['message']='Successfully Saved';
        return response()->json($response,200);
    }


    public function remove($id){
        $salaryTypes = SalaryType::find($id);
        $salaryTypes->delete();
        return response()->json($salaryTypes,200);
    }


}
