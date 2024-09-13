<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\PayItem;

class PayItemController extends Controller
{

    public function getPayItems(){
        $payItems=PayItem::get();
        return response()->json($payItems,200);
    }

    public function savePayItem(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'code' => 'required|string',
            'is_benefit_in_kind' => 'nullable|array',
            'taxable' => 'nullable|array',
            'pensionable' => 'nullable|array',
            'subject_to_national_insurance' => 'nullable|array',
            'payment_type' => 'required|array|in:Gross Addition,Gross Deduction,Net Addition,Net Deduction',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $payItem=new PayItem();
        $payItem->name=$request->name;
        $payItem->code=$request->code;
        $payItem->is_benefit_in_kind=$request->is_benefit_in_kind;
        $payItem->taxable=$request->taxable ? ($request->taxable[0]=='taxable' ? true:false):false;
        $payItem->pensionable=$request->pensionable ? ($request->pensionable[0]=='pensionable' ? true:false):false;
        $payItem->subject_to_national_insurance=$request->subject_to_national_insurance ? ($request->subject_to_national_insurance[0]=='subject_to_national_insurance' ? true:false):false;
        $payItem->payment_type=$request->payment_type['name'];
        $payItem->save();
        $response['message']='Successfully Saved';
        return response()->json($response,200);
    }

    public function editPayItem($id){
        $payItem=PayItem::find($id);
        return response()->json($payItem,200);
    }

    public function updatePayItem(Request $request,$id){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'code' => 'required|string',
            'is_benefit_in_kind' => 'nullable|array',
            'taxable' => 'nullable|array',
            'pensionable' => 'nullable|array',
            'subject_to_national_insurance' => 'nullable|array',
            'payment_type' => 'required|array|in:Gross Addition,Gross Deduction,Net Addition,Net Deduction',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $payItem=PayItem::find($id);
        $payItem->name=$request->name;
        $payItem->code=$request->code;
        $payItem->is_benefit_in_kind=$request->is_benefit_in_kind;
        $payItem->taxable=$request->taxable ? ($request->taxable[0]=='taxable' ? true:false):false;
        $payItem->pensionable=$request->pensionable ? ($request->pensionable[0]=='pensionable' ? true:false):false;
        $payItem->subject_to_national_insurance=$request->subject_to_national_insurance ? ($request->subject_to_national_insurance[0]=='subject_to_national_insurance' ? true:false):false;
        $payItem->payment_type=$request->payment_type['name'];
        $payItem->save();
        $response['message']='Successfully Updated';
        return response()->json($response,200);
    }
}
