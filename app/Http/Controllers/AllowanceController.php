<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Allowance;
use App\Repositories\Interfaces\RealTimeInformationInterface;

class AllowanceController extends Controller
{
    private $RealTimeInformationRepository;

    public function __construct(RealTimeInformationInterface $RealTimeInformationRepository) {
        $this->RealTimeInformationRepository = $RealTimeInformationRepository;
    }

    public function getAllowances(){
        $allowance=Allowance::where('type','employment_allowance')->first();
        $apprenticeship_levy=Allowance::where('type','apprenticeship_levy')->first();
        $response['employment_allowance']=$allowance;
        $response['apprenticeship_levy']=$apprenticeship_levy;
        return response()->json($response,200);
    }

    public function enableEmploymentAllowance(Request $request){
        $allowance=Allowance::where('type','employment_allowance')->first();
        if($allowance){
            $allowance = $allowance;
        }else{
            $allowance=new Allowance();
        }

        $validator = Validator::make($request->all(), [
            'allowance_claimed' => 'required|numeric',
            'allowance_remaining' => 'required|numeric',
            'status' => 'required|string|in:enabled,disabled',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }

        // try{
            $allowance->allowance_claimed=$request->allowance_claimed;
            $allowance->allowance_remaining=$request->allowance_remaining;
            $allowance->type='employment_allowance';
            $allowance->status=$request->status;
            $allowance->save();

            $isAllowanceIndicator=$request->status==='enabled'? true:false;
            $result=$this->RealTimeInformationRepository->AllowanceEPSSubmission($isAllowanceIndicator);
            return $result;
            $response['message']='Successfully Saved';
            return response()->json($response,200);
        // } catch (\Exception $exception) {
        //     if (('APP_ENV') == 'local') {
        //         dd($exception);
        //     }
        // }
    }

    public function enableApprenticeshipLevy(Request $request){
        $allowance=Allowance::where('type','apprenticeship_levy')->first();
        if($allowance){
            $allowance = $allowance;
        }else{
            $allowance=new Allowance();
        }

        $validator = Validator::make($request->all(), [
            'pay_bill_ytd' => 'required|numeric',
            'levy_due_ytd' => 'required|numeric',
            'shares_apprentice_levy_allowance' => 'nullable|string',
            'status' => 'required|string|in:enabled,disabled',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $allowance->pay_bill_ytd=$request->pay_bill_ytd;
        $allowance->levy_due_ytd=$request->levy_due_ytd;
        $allowance->type='apprenticeship_levy';
        $allowance->status=$request->status;
        $allowance->save();
        $response['message']='Successfully Saved';
        return response()->json($response,200);
    }
}
