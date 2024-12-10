<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeYearToDates;
use Illuminate\Http\Request;

class EmployeeYearToDatesController extends Controller
{
    public function getEmployeeYTD($id){
        $ytd=EmployeeYearToDates::where('employee_id',$id)->first();
        return response()->json($ytd,200);
    }
    
    public function storeYTD(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            //TAX
            'employee_id' => 'required|integer',
            'gross_for_tax' => 'required|numeric',  
            'tax_deducted' => 'required|numeric',  
            'student_loan' => 'required|numeric',  
            'postgraduate_loan' => 'required|numeric',  
            'employee_pension' => 'required|numeric',          
            'employer_pension' => 'required|numeric',  
            
            //BENEFITS
            'benefit_in_kind_payrolled_amount' => 'required|numeric',  
            'employe_net_pay_pension' => 'required|numeric',  

            //Statutory Payments
            'statutory_maternity_pay' => 'required|numeric',  
            'statutory_paternity_pay' => 'required|numeric',  
            'statutory_adoption_pay' => 'required|numeric',          
            'statutory_sick_pay' => 'required|numeric', 
            'parental_bereavement' => 'required|numeric',          
            'shared_parental_pay' => 'required|numeric', 

            //National Insurance
            'national_insurance_category' => 'required|array',  
            'earnings_at_LEL' => 'required|numeric',  
            'earnings_at_PT' => 'required|numeric',          
            'earnings_to_UEL' => 'required|numeric', 
            'employee_national_insurance' => 'required|numeric',          
            'employer_national_insurance' => 'required|numeric', 
            'gross_pay_for_national_insurance' => 'required|numeric', 

            //National Insurance Director
            'director_earnings_at_LEL' => 'required|numeric',  
            'director_earnings_to_PT' => 'required|numeric',          
            'director_earnings_to_UEL' => 'required|numeric', 
            'director_national_insurance' => 'required|numeric',          
            'director_employer_national_insurance' => 'required|numeric', 
            'director_gross_pay_for_national_insurance' => 'required|numeric', 
        ]);

        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }

        $ytd=EmployeeYearToDates::where('employee_id',$request->employee_id)->first();
        if($ytd){

        }else{
            $ytd=new EmployeeYearToDates();    
        }

         //TAX
         $ytd->employee_id = $request->employee_id;
         $ytd->gross_for_tax = $request->gross_for_tax; 
         $ytd->tax_deducted = $request->tax_deducted; 
         $ytd->student_loan = $request->student_loan; 
         $ytd->postgraduate_loan = $request->postgraduate_loan;  
         $ytd->employee_pension = $request->employee_pension;       
         $ytd->employer_pension = $request->employer_pension;
         
        // Statutory Payments
        $ytd->statutory_maternity_pay = $request->statutory_maternity_pay;  
        $ytd->statutory_paternity_pay = $request->statutory_paternity_pay;  
        $ytd->statutory_adoption_pay = $request->statutory_adoption_pay;          
        $ytd->statutory_sick_pay = $request->statutory_sick_pay; 
        $ytd->parental_bereavement = $request->parental_bereavement;          
        $ytd->shared_parental_pay = $request->shared_parental_pay; 

        // National Insurance
        $ytd->national_insurance_category = $request->national_insurance_category['code'];  
        $ytd->earnings_at_LEL = $request->earnings_at_LEL;  
        $ytd->earnings_at_PT = $request->earnings_at_PT;          
        $ytd->earnings_to_UEL = $request->earnings_to_UEL; 
        $ytd->employee_national_insurance = $request->employee_national_insurance;          
        $ytd->employer_national_insurance = $request->employer_national_insurance;
        $ytd->gross_pay_for_national_insurance = $request->gross_pay_for_national_insurance;

        // National Insurance Director
        $ytd->director_earnings_at_LEL = $request->director_earnings_at_LEL;  
        $ytd->director_earnings_to_PT = $request->director_earnings_to_PT;          
        $ytd->director_earnings_to_UEL = $request->director_earnings_to_UEL; 
        $ytd->director_national_insurance = $request->director_national_insurance;          
        $ytd->director_employer_national_insurance = $request->director_employer_national_insurance; 
        $ytd->director_gross_pay_for_national_insurance = $request->director_gross_pay_for_national_insurance;

        $ytd->save();

        $response['message']='Successfully Saved';
        return response()->json($response,200);
    }
}
