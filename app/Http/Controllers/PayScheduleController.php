<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\PaySchedule;

class PayScheduleController extends Controller
{
    public function index(){
        $paySchedules=PaySchedule::orderBy('created_at', 'desc')->get();
        return response()->json($paySchedules,200);
    }

    public function save(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'pay_frequency' => 'required|array',
            'paydays' => 'required|array',
            'first_paydate' => 'required',
            'day_rate_method' => 'required|array',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $paySchedule=new PaySchedule();
        $paySchedule->name=$request->name;
        $paySchedule->pay_frequency=$request->pay_frequency['name'];
        $paySchedule->paydays=$request->paydays['name'];
        $paySchedule->first_paydate=$request->first_paydate;
        $paySchedule->day_rate_method=$request->day_rate_method['name'];
        $paySchedule->save();
        $response['message']='Successfully Saved';
        return response()->json($response,200);
    }

    public function edit($id){
        $paySchedule=PaySchedule::find($id);
        return response()->json($paySchedule,200);
    }

    public function update(Request $request,$id){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'pay_frequency' => 'required|array',
            'paydays' => 'required|array',
            'first_paydate' => 'required',
            'day_rate_method' => 'required|array',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $paySchedule=PaySchedule::find($id);
        $paySchedule->name=$request->name;
        $paySchedule->pay_frequency=$request->pay_frequency['name'];
        $paySchedule->paydays=$request->paydays['name'];
        $paySchedule->first_paydate=$request->first_paydate;
        $paySchedule->day_rate_method=$request->day_rate_method['name'];
        $paySchedule->save();
        $response['message']='Successfully Updated';
        return response()->json($response,200);
    }

    public function delete($id){
        $paySchedule=PaySchedule::find($id);
        $paySchedule->delete();
        $response['message']='Successfully Deleted';
        return response()->json($response,200);
    }

    public function changeStatus(Request $request,$id){
        $validator = Validator::make($request->all(), [
            'status' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $paySchedule=PaySchedule::find($id);
        $paySchedule->status=$request->status;
        $paySchedule->save();
        $response['message']='Successfully Updated';
        return response()->json($response,200);
    }
}
