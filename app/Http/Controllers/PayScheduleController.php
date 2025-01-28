<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\PaySchedule;
use App\Models\Payroll;
use Carbon\Carbon;

class PayScheduleController extends Controller
{
    public function index(){
        $paySchedules=PaySchedule::withCount('payScheduleEmployees')->orderBy('created_at', 'desc')->get();
        return response()->json($paySchedules,200);
    }

    public function getPayScheduleDropdown(){
        $paySchedules = PaySchedule::orderBy('created_at', 'desc')->get()
        ->map(function($schedule) {
            return [
                'name' => $schedule->name,
                'code' => $schedule->id 
            ];
        });
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

        $inputDate = Carbon::parse($request->first_paydate);
        if ($request->pay_frequency['name'] == 'Monthly') {
            $startDate = $inputDate->copy()->subMonth()->addDay(1);
            $endDate = $inputDate->endOfMonth();
        } elseif ($request->pay_frequency['name'] == 'Weekly') {
            $startDate = $inputDate->copy()->subWeek()->addDay(1);
            $endDate = $inputDate->copy()->addWeek();
        } elseif ($request->pay_frequency['name'] == 'Fortnightly') {
            $startDate = $inputDate->copy()->subWeeks(2)->addDay(1);
            $endDate = $inputDate->copy()->addWeek();
        } elseif ($request->pay_frequency['name'] == 'Four Weekly') {
            $startDate = $inputDate->copy()->subWeeks(4)->addDay(1);
            $endDate = $inputDate->copy()->addWeek();
        }

        // Calculate tax period based on the tax year start (6th April)
        $taxYearStart = Carbon::create($inputDate->year, 4, 6);
        if ($inputDate->lessThan($taxYearStart)) {
            $taxYearStart->subYear();  // Adjust to previous tax year if before 6th April
        }

        // Find the tax period
        $tax_period = null;
        if ($request->pay_frequency['name'] == 'Monthly') {
            $monthsSinceStart = $taxYearStart->diffInMonths($endDate);
            $tax_period = floor($monthsSinceStart);
        } elseif ($request->pay_frequency['name'] == 'Weekly') {
            $weeksSinceStart = $taxYearStart->diffInWeeks($endDate);
            $tax_period = floor($weeksSinceStart);
        } elseif ($request->pay_frequency['name'] == 'Fortnightly') {
            $fortnightsSinceStart = $taxYearStart->diffInWeeks($endDate);
            $tax_period = floor($fortnightsSinceStart);
        } elseif ($request->pay_frequency['name'] == 'Four Weekly') {
            $fourWeeksSinceStart = $taxYearStart->diffInWeeks($endDate);
            $tax_period = floor($fourWeeksSinceStart);
        }

        $payroll=new Payroll();
        $payroll->pay_schedule_id=$paySchedule->id;
        $payroll->tax_period=$tax_period;
        $payroll->pay_run_start_date=$startDate;
        $payroll->pay_run_end_date=$request->first_paydate;;
        $payroll->pay_date=$request->first_paydate;
        $payroll->status="draft";
        $payroll->save();

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
