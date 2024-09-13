<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\StatutoryPay;

class StatutorypayController extends Controller
{

    public function getStatutoryPay(){
        $statutoryPay=StatutoryPay::first();
        return response()->json($statutoryPay,200);
    }
    
    public function save(Request $request){
        $statutoryPay=StatutoryPay::first();
        if($statutoryPay){
            $statutoryPay = $statutoryPay;
        }else{
            $statutoryPay=new StatutoryPay();
        }

        $validator = Validator::make($request->all(), [
            'recovered_smp_ytd' => 'required|numeric',
            'smp_ni_compensation_ytd' => 'required|numeric',
            'recovered_spp_ytd' => 'required|numeric',
            'spp_ni_compensation_ytd' => 'required|numeric|',
            'recovered_sap_ytd' => 'required|numeric',
            'sap_ni_compensation_ytd' => 'required|numeric',
            'recovered_spbp_ytd' => 'required|numeric',
            'spbp_ni_compensation_ytd' => 'required|numeric|',
            'recovered_shpp_ytd' => 'required|numeric',
            'shpp_ni_compensation_ytd' => 'required|numeric|',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $statutoryPay->recovered_smp_ytd=$request->recovered_smp_ytd;
        $statutoryPay->smp_ni_compensation_ytd=$request->smp_ni_compensation_ytd;
        $statutoryPay->recovered_spp_ytd=$request->recovered_spp_ytd;
        $statutoryPay->spp_ni_compensation_ytd=$request->spp_ni_compensation_ytd;
        $statutoryPay->recovered_sap_ytd=$request->recovered_sap_ytd;
        $statutoryPay->sap_ni_compensation_ytd=$request->sap_ni_compensation_ytd;
        $statutoryPay->recovered_spbp_ytd=$request->recovered_spbp_ytd;
        $statutoryPay->spbp_ni_compensation_ytd=$request->spbp_ni_compensation_ytd;
        $statutoryPay->recovered_shpp_ytd=$request->recovered_shpp_ytd;
        $statutoryPay->shpp_ni_compensation_ytd=$request->shpp_ni_compensation_ytd;
        $statutoryPay->save();
        $response['message']='Successfully Saved';
        return response()->json($response,200);
    }
}
