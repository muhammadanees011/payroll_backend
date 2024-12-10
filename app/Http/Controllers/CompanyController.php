<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\HmrcSetting;
use App\Models\PaySchedule;
use Illuminate\Support\Facades\Crypt;

class CompanyController extends Controller
{
    public function createCompany(Request $request){
        $company=Company::first();
        if($company){
            $step = $company->step + 1;
        }else{
            $step = 1;
            $company=new Company();
        }

        switch ($step) {
            case 1:
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string|max:255',
                    'legal_structure' => 'required|array',
                    'post_code' => 'required|string|max:255',
                    'address_line_1' => 'required|string|max:255',
                    'address_line_2' => 'nullable|string|max:255',
                    'city' => 'required|string|max:255',
                    'director_name' => 'required|string|max:255',
                    'authorized_to_act' => 'nullable|array',
                    'agreed_to_terms' => 'nullable|array',
                ]);
                if ($validator->fails())
                {
                    return response()->json(['errors'=>$validator->errors()->all()], 422);
                }
                $company->name=$request->name;
                $company->legal_structure=$request->legal_structure['name'];
                $company->post_code=$request->post_code;
                $company->address_line_1=$request->address_line_1;
                $company->address_line_2=$request->address_line_2;
                $company->city=$request->city;
                $company->director_name=$request->director_name;
                $company->authorized_to_act = $request->authorized_to_act[0] && $request->authorized_to_act[0] == 'authorized_to_act' ? true : false;
                $company->agreed_to_terms=$request->agreed_to_terms[0] && $request->agreed_to_terms[0] == 'agreed_to_terms' ? true : false;
                $company->step=1;
                $company->save();
                $response['message']='Successfully Saved';
                $response['step']=1;
                return response()->json($response,200);
            case 2:
                $validator = Validator::make($request->all(), [
                    'company_payee' => 'required|array',
                    'first_payday' => 'required|date',
                    'is_first_payday_of_year' => 'required|string',
                ]);
                if ($validator->fails())
                {
                    return response()->json(['errors'=>$validator->errors()->all()], 422);
                }
                $company->company_payee=$request->company_payee;
                $company->first_payday=$request->first_payday;
                $company->is_first_payday_of_year=$request->is_first_payday_of_year=='yes' ? true :false;
                $company->step=2;
                $company->save();
                $response['message']='Successfully Saved';
                $response['step']=2;
                return response()->json($response,200);
            case 3:
                $validator = Validator::make($request->all(), [
                    'account_office_reference' => 'required|string|max:255',
                    'paye_reference' => 'required|string|max:255',
                    'taxpayer_reference' => 'required|string|max:255',
                    'employment_allowance' => 'required|array',
                    'business_sector' => 'required|array',
                    'hmrc_gateway_id' => 'required|string|max:255',
                    'hmrc_password' => 'required|string|max:255',
                ]);
                if ($validator->fails())
                {
                    return response()->json(['errors'=>$validator->errors()->all()], 422);
                }
                $hmrc=new HmrcSetting();
                $hmrc->account_office_reference=$request->account_office_reference;
                $hmrc->paye_reference=$request->paye_reference;
                $hmrc->taxpayer_reference=$request->taxpayer_reference;
                $hmrc->employment_allowance=json_encode($request->employment_allowance);
                $hmrc->business_sector=json_encode($request->business_sector);
                $hmrc->hmrc_gateway_id=$request->hmrc_gateway_id;
                $hmrc->hmrc_password=Crypt::encryptString($request->hmrc_password);
                // $decryptedPassword = Crypt::decryptString($thirdPartyApp->password); //to decrypt the password string
                $hmrc->save();
                $company->step=3;
                $company->save();
                $response['message']='Successfully Saved';
                $response['step']=3;
                return response()->json($response,200);
            case 4:
                $validator = Validator::make($request->all(), [
                    'payschedule_name' => 'required|string',
                    'pay_frequency' => 'required|array',
                    'paydays' => 'required|array',
                    'first_paydate' => 'required|date',
                    'day_rate_method' => 'required|array',
                ]);
                if ($validator->fails())
                {
                    return response()->json(['errors'=>$validator->errors()->all()], 422);
                }
                $payschedule=new PaySchedule();
                $payschedule->name=$request->payschedule_name;
                $payschedule->pay_frequency=$request->pay_frequency['name'];
                $payschedule->paydays=$request->paydays['name'];
                $payschedule->first_paydate=$request->first_paydate;
                $payschedule->day_rate_method=$request->day_rate_method['name'];
                $payschedule->save();
                $company->step=4;
                $company->save();
                $response['message']='Successfully Saved';
                $response['step']=4;
                return response()->json($response,200);
        }
        // try{
        // DB::beginTransaction();
        // DB::commit();
        // $response ='';
        // return response()->json($response, 200);
        // } catch (\Exception $exception) {
        //     DB::rollback();
        //     if (('APP_ENV') == 'local') {
        //         dd($exception);
        //     }
        // }
    }

    public function updateDetails(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'legal_structure' => 'required|array',
            'post_code' => 'required|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'director_name' => 'required|string|max:255',
            'authorized_to_act' => 'nullable|array',
            'agreed_to_terms' => 'nullable|array',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $company=Company::first();
        $company->name=$request->name;
        $company->legal_structure=$request->legal_structure['name'];
        $company->post_code=$request->post_code;
        $company->address_line_1=$request->address_line_1;
        $company->address_line_2=$request->address_line_2;
        $company->city=$request->city;
        $company->director_name=$request->director_name;
        $company->authorized_to_act = $request->authorized_to_act[0] && $request->authorized_to_act[0] == 'authorized_to_act' ? true : false;
        $company->agreed_to_terms=$request->agreed_to_terms[0] && $request->agreed_to_terms[0] == 'agreed_to_terms' ? true : false;
        $company->step=1;
        $company->save();
        $response['message']='Successfully Saved';
        $response['step']=1;
        return response()->json($response,200);
    }

    public function getStep(){
        $company=Company::first();
        if($company){
            $response['step']=$company->step;
        }else{
            $response['step']=0;
        }
        return response()->json($response,200);
    }

    public function getCompanyDetails(){
        $company=Company::first();
        return response()->json($company,200);
    }

    public function getHMRCSettings(){
        $settings=HmrcSetting::first();
        if ($settings && $settings->hmrc_password) {
            $settings->hmrc_password = Crypt::decryptString($settings->hmrc_password);
        }
        return response()->json($settings,200);
    }

    public function updateHMRCSettings(Request $request){
        $validator = Validator::make($request->all(), [
            'account_office_reference' => 'required|string|max:255',
            'paye_reference' => 'required|string|max:255',
            'taxpayer_reference' => 'nullable|string|max:255',
            'employment_allowance' => 'nullable|array',
            'business_sector' => 'nullable|array',
            'hmrc_gateway_id' => 'required|string|max:255',
            'hmrc_password' => 'nullable|string|max:255',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $hmrc= HmrcSetting::first();
        $hmrc->account_office_reference=$request->account_office_reference;
        $hmrc->paye_reference=$request->paye_reference;
        $hmrc->taxpayer_reference=$request->taxpayer_reference;
        $hmrc->employment_allowance=json_encode($request->employment_allowance);
        $hmrc->business_sector=json_encode($request->business_sector);
        $hmrc->hmrc_gateway_id=$request->hmrc_gateway_id;
        $hmrc->hmrc_password=Crypt::encryptString($request->hmrc_password);
        $hmrc->save();
    }
}
