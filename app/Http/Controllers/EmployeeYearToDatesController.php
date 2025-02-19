<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeYearToDates;
use App\Models\PayrollEmployee;
use App\Models\P32TaxesFilings;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\NICCalculator;

class EmployeeYearToDatesController extends Controller
{
    protected $nicCalculator;

    public function __construct(NICCalculator $nicCalculator)
    {
        $this->nicCalculator = $nicCalculator;
    }

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


    public function savePayrollEmployeesYTD(Request $request){        
        $employees = PayrollEmployee::with('employee', 'employementdetail', 'payroll', 'payschedule')
        ->where('pay_schedule_id', $request->payschedule_id)
        ->orderBy('created_at', 'desc')
        ->get();

        foreach ($employees as $employee) {
            $ytd = EmployeeYearToDates::where('employee_id', $employee->employee_id)->first();
            if ($ytd) {
                
                //TAX
                $ytd->gross_for_tax = ($ytd->gross_for_tax ?? 0) + ($employee->gross_pay ?? 0); 
                $ytd->tax_deducted = ($ytd->tax_deducted ?? 0) + ($employee->paye_income_tax ?? 0); 
                $ytd->student_loan = ($ytd->student_loan ?? 0) + ($employee->student_loan ?? 0); 
                $ytd->postgraduate_loan = ($ytd->postgraduate_loan ?? 0) + ($employee->pg_loan ?? 0);  
                $ytd->employee_pension = ($ytd->employee_pension ?? 0) + ($employee->employee_pension ?? 0);       
                $ytd->employer_pension = ($ytd->employer_pension ?? 0) + ($employee->employer_pension ?? 0);                
                
                // Statutory Payments
                $ytd->statutory_maternity_pay = ($ytd->statutory_maternity_pay ?? 0) + 0;  //this
                $ytd->statutory_paternity_pay = ($ytd->statutory_paternity_pay ?? 0) + ($employee->paternity_pay ?? 0);  
                $ytd->statutory_adoption_pay = ($ytd->statutory_adoption_pay ?? 0) + 0;    //this      
                $ytd->statutory_sick_pay = ($ytd->statutory_sick_pay ?? 0) + ($employee->sick_pay ?? 0); 
                $ytd->parental_bereavement = ($ytd->parental_bereavement ?? 0) + 0;   //this       
                $ytd->shared_parental_pay = ($ytd->shared_parental_pay ?? 0) + 0;  //this

                // National Insurance
                $ytd->national_insurance_category = $employee->employee->ni_category ?? null;
                $ytd->earnings_at_LEL = ($ytd->earnings_at_LEL ?? 0) + $this->nicCalculator->calculateLEL($employee->gross_pay, $employee->payschedule->pay_frequency);
                $ytd->earnings_at_PT = ($ytd->earnings_at_PT ?? 0) + $this->nicCalculator->calculatePT($employee->gross_pay, $employee->payschedule->pay_frequency);
                $ytd->earnings_to_UEL = ($ytd->earnings_to_UEL ?? 0) + $this->nicCalculator->calculateUEL($employee->gross_pay, $employee->payschedule->pay_frequency);
                $ytd->employee_national_insurance = ($ytd->employee_national_insurance ?? 0) + ($employee->employee_nic ?? 0);
                $ytd->employer_national_insurance = ($ytd->employer_national_insurance ?? 0) + ($employee->employer_nic ?? 0);
                $ytd->gross_pay_for_national_insurance = ($ytd->gross_pay_for_national_insurance ?? 0) + ($employee->gross_pay ?? 0);        

                if($employee->employementdetail->date_appointed_director &&
                    $employee->employementdetail->date_ended_director &&
                    $employee->employementdetail->date_ended_director <= $employee->payroll->pay_date
                  ){
                // National Insurance Director
                    $ytd->director_earnings_at_LEL = ($ytd->director_earnings_at_LEL?? 0) + $this->nicCalculator->calculateLEL($employee->gross_pay, $employee->payschedule->pay_frequency);  
                    $ytd->director_earnings_to_PT = ($ytd->director_earnings_to_PT?? 0) + $this->nicCalculator->calculatePT($employee->gross_pay, $employee->payschedule->pay_frequency);          
                    $ytd->director_earnings_to_UEL = ($ytd->director_earnings_to_UEL?? 0) + $this->nicCalculator->calculateUEL($employee->gross_pay, $employee->payschedule->pay_frequency); 
                    $ytd->director_national_insurance = ($ytd->director_national_insurance?? 0) + ($employee->employee_nic ?? 0);
                    $ytd->director_employer_national_insurance = ($ytd->director_employer_national_insurance?? 0) + ($employee->employer_nic ?? 0); 
                    $ytd->director_gross_pay_for_national_insurance = ($ytd->director_gross_pay_for_national_insurance?? 0) + ($employee->gross_pay ?? 0);
                }

                $ytd->save();
            } else {
                $ytd = new EmployeeYearToDates();
                $ytd->employee_id = $employee->employee_id;

                //TAX
                $ytd->gross_for_tax = $employee->gross_pay ?? 0; 
                $ytd->tax_deducted = $employee->paye_income_tax ?? 0; 
                $ytd->student_loan = $employee->student_loan ?? 0; 
                $ytd->postgraduate_loan = $employee->pg_loan ?? 0;  
                $ytd->employee_pension = $employee->employee_pension ?? 0;       
                $ytd->employer_pension = $employee->employer_pension ?? 0;                
                
                // Statutory Payments
                $ytd->statutory_maternity_pay = 0;  //this
                $ytd->statutory_paternity_pay = $employee->paternity_pay ?? 0;  
                $ytd->statutory_adoption_pay = 0;    //this      
                $ytd->statutory_sick_pay = $employee->sick_pay ?? 0; 
                $ytd->parental_bereavement = 0;   //this       
                $ytd->shared_parental_pay = 0;  //this

                // National Insurance
                $ytd->national_insurance_category = $employee->employee->ni_category ?? null;
                $ytd->earnings_at_LEL = $this->nicCalculator->calculateLEL($employee->gross_pay, $employee->payschedule->pay_frequency);
                $ytd->earnings_at_PT = $this->nicCalculator->calculatePT($employee->gross_pay, $employee->payschedule->pay_frequency);
                $ytd->earnings_to_UEL = $this->nicCalculator->calculateUEL($employee->gross_pay, $employee->payschedule->pay_frequency);
                $ytd->employee_national_insurance = $employee->employee_nic ?? 0;
                $ytd->employer_national_insurance = $employee->employer_nic ?? 0;
                $ytd->gross_pay_for_national_insurance = $employee->gross_pay ?? 0;        

                if($employee->employementdetail->date_appointed_director &&
                    $employee->employementdetail->date_ended_director &&
                    $employee->employementdetail->date_ended_director <= $employee->payroll->pay_date
                    ){
                // National Insurance Director
                    $ytd->director_earnings_at_LEL = $this->nicCalculator->calculateLEL($employee->gross_pay, $employee->payschedule->pay_frequency);  
                    $ytd->director_earnings_to_PT =  $this->nicCalculator->calculatePT($employee->gross_pay, $employee->payschedule->pay_frequency);          
                    $ytd->director_earnings_to_UEL = $this->nicCalculator->calculateUEL($employee->gross_pay, $employee->payschedule->pay_frequency); 
                    $ytd->director_national_insurance = $employee->employee_nic ?? 0;
                    $ytd->director_employer_national_insurance = $employee->employer_nic ?? 0; 
                    $ytd->director_gross_pay_for_national_insurance = $employee->gross_pay ?? 0;
                }

                $ytd->save();
            }
            
        }

        $this->savePaySummary($request->payschedule_id);
        return response()->json('saved',200);
    }


    public function savePaySummary($payschedule_id)
    {
        $employees = PayrollEmployee::with('employee', 'employementdetail', 'payroll', 'payschedule')
        ->where('pay_schedule_id', $payschedule_id)
        ->get();

        if($employees){
            $startDate=$employees[0]->payroll->pay_run_start_date;
            $endDate=$employees[0]->payroll->pay_run_end_date;
        }else{
            return;
        }

        $taxMonth=$this->getTaxMonthNumber($startDate, $endDate);

        $p32taxesfilings = P32TaxesFilings::where('tax_month',$taxMonth)->first();
        $p32taxesfilings->claimed_employment_allowance += 5000;

        foreach ($employees as $employee) {
            //----------
            $p32taxesfilings->total_paye += 
            $employee->paye_income_tax
            + $employee->employee_nic 
            + $employee->employer_nic 
            + $employee->student_loan 
            + $employee->pg_loan;
            //-------------
            $p32taxesfilings->gross_national_insurance += $employee->employee_nic + $employee->employer_nic;
            //------------
            $p32taxesfilings->total_statutory_recoveries += $employee->paternity_pay;

            $p32taxesfilings->save();

        }

        $p32taxesfilings->amount_due = $p32taxesfilings->claimed_employment_allowance;
        $p32taxesfilings->save();
    }

    function getTaxMonthNumber($startDate, $endDate) {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        // Tax year starts on April 6
        $taxYearStart = Carbon::create($start->year, 4, 6);
        // If the start date is before April 6, adjust to the previous tax year
        if ($start->lessThan($taxYearStart)) {
            $taxYearStart->subYear();
        }
        // Calculate the tax month based on the start date
        $taxMonth = floor($taxYearStart->diffInDays($start) / 30.4375) + 1;
        return min(max(1, (int)$taxMonth), 12); // Ensure it's between 1 and 12
    }
    
}
