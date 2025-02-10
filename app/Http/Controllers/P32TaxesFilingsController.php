<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\P32TaxesFilings;
use Illuminate\Support\Facades\Validator;

class P32TaxesFilingsController extends Controller
{
    public function getP32TaxesFilings(Request $request){
        $validator = Validator::make($request->all(), [
            'tax_year' => 'nullable',
            'from_tax_month' => 'nullable',
            'to_tax_month' => 'nullable',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }

        if($request->from_tax_month && $request->to_tax_month){
            $P32TaxesFilings=P32TaxesFilings::where('tax_year',$request->tax_year['code'])
            ->whereBetween('tax_month', [$request->from_tax_month['code'], $request->to_tax_month['code']])
            ->get();
        }else if($request->from_tax_month && $request->to_tax_month==null){
            $P32TaxesFilings = P32TaxesFilings::where('tax_year',$request->tax_year['code'])
            ->where('tax_month', '>=', $request->from_tax_month['code'])->get();
        }else if ($request->from_tax_month==null && $request->to_tax_month ) {
            $P32TaxesFilings=P32TaxesFilings::where('tax_year',$request->tax_year['code'])
            ->whereBetween('tax_month', [1, $request->to_tax_month['code']])->get();
        }else{
            $P32TaxesFilings=P32TaxesFilings::where('tax_year',$request->tax_year['code'])->get();
        }

        // Compute totals for numeric columns
        $totalRow = [
            'tax_month' => 'Total', // Label for the total row
            'tax_year' => $request->tax_year['code'],
            'total_paye' => $P32TaxesFilings->sum('total_paye'),
            'gross_national_insurance' => $P32TaxesFilings->sum('gross_national_insurance'),
            'claimed_employment_allowance' => $P32TaxesFilings->sum('claimed_employment_allowance'),
            'total_statutory_recoveries' => $P32TaxesFilings->sum('total_statutory_recoveries'),
            'apprentice_levy' => $P32TaxesFilings->sum('apprentice_levy'),
            'cis_deductions' => $P32TaxesFilings->sum('cis_deductions'),
            'amount_due' => $P32TaxesFilings->sum('amount_due'),
        ];
        // Append total row at the end
        $P32TaxesFilings->push($totalRow);

        return response()->json($P32TaxesFilings,200);
    }
}
