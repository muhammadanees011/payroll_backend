<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\EmployeeStarterDetail;
use App\Models\EmployementDetail;
use App\Models\EmployeeNICategory;
use App\Models\EmploymentStatutoryPaymentsLoans;
use App\Models\EmployeePension;
use App\Models\EmployeePaternityLeave;
use App\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\Payroll_Interface;

class EmployeeController extends Controller
{
    private $Payroll_Repository;

    public function __construct(Payroll_Interface $Payroll_Repository)
    {
        $this->Payroll_Repository = $Payroll_Repository;
    }


    public function getEmployees(){
        $employees = EmployeeResource::collection(
            Employee::get()
        );
        return response()->json($employees,200);
    }


    public function createEmployee(Request $request, $employee_id=null){ 
        $employee=Employee::find($employee_id);
        if($employee){
            $step = $employee->step +1;
        }else{
            $step = 1;
            $employee=new Employee();
        }
        switch ($step) {
            case 1:
                $validator = Validator::make($request->all(), [
                    'title' => 'required|array',
                    'forename' => 'required|string',
                    'surname' => 'required|string|max:255',
                    'gender' => 'required|array',
                    'dob' => 'nullable|date',
                    'work_email' => 'required|email',
                    'telephone' => 'nullable|string|max:255',
                    'ni_category' => 'required|array',
                    'nino' => 'nullable|string',
                    'postcode' => 'required|string|max:255',
                    'address_line1' => 'required|string|max:255',
                    'address_line2' => 'nullable|string',
                    'city' => 'required|string',
                    'country' => 'required|string',
                ]);
                if ($validator->fails())
                {
                    return response()->json(['errors'=>$validator->errors()->all()], 422);
                }
                $employee->title=$request->title['code'];
                $employee->forename=$request->forename;
                $employee->surname=$request->surname;
                $employee->gender=$request->gender['code'];
                $employee->dob=$request->dob;
                $employee->work_email=$request->work_email;
                $employee->telephone=$request->telephone;
                $employee->ni_category = $request->ni_category['code'];
                $employee->nino = $request->nino;
                $employee->postcode=$request->postcode;
                $employee->address_line1=$request->address_line1;
                $employee->address_line2=$request->address_line2;
                $employee->city = $request->city;
                $employee->country = $request->country;
                $employee->status='Pending Information';
                $employee->save();
                $employee_id=$employee->id;
                $response['message']='Successfully Saved';
                $response['employee_id']=$employee_id;
                $response['step']=2;
                return response()->json($response,200);
            case 2:
                $validator = Validator::make($request->all(), [
                    'pay_schedule_id' => 'required|array',
                    'payroll_id' => 'required',
                    'employement_start_date' => 'required|date',
                    'salary_type' => 'required|array',
                    'anual_salary' => 'nullable',
                    'monthly_salary' => 'nullable',
                    'weekly_salary' => 'nullable',
                    'expected_work_hours_per_week' => 'required',
                    'hourly_equivalent' => 'nullable',
                    'is_director_current_tax_year' => 'required',
                    'date_appointed_director' => 'nullable|date',
                    'date_ended_directorship' => 'nullable|date',          
                    'calculation_method' => 'nullable|array',                              
                ]);
                if ($validator->fails())
                {
                    return response()->json(['errors'=>$validator->errors()->all()], 422);
                }
                $employmentDetail= new EmployementDetail();
                $employmentDetail->employee_id=$employee_id;
                $employmentDetail->pay_schedule_id=$request->pay_schedule_id['code'];
                $employmentDetail->salary_type=$request->salary_type['code'];
                $employmentDetail->anual_salary=$request->anual_salary;
                $employmentDetail->monthly_salary=$request->monthly_salary;
                $employmentDetail->weekly_salary=$request->weekly_salary;
                $employmentDetail->expected_work_hours_per_week=$request->expected_work_hours_per_week;
                $employmentDetail->hourly_equivalent=$request->hourly_equivalent;
                $employmentDetail->is_director_current_tax_year=$request->is_director_current_tax_year;
                $employmentDetail->date_appointed_director=$request->date_appointed_director;
                $employmentDetail->date_ended_directorship=$request->date_ended_directorship;
                $employmentDetail->calculation_method=$request->calculation_method ? $request->calculation_method['code'] : null;
                $employmentDetail->save();
                $employee->step=2;
                $employee->payroll_id=$request->payroll_id;
                $employee->employement_start_date=$request->employement_start_date;
                $employee->status='Pending Information';
                $employee->save();
                $response['employee_id']=$employee_id;
                $response['message']='Successfully Saved';
                $response['step']=2;
                return response()->json($response,200);
            case 3:
                $validator = Validator::make($request->all(), [
                    'starter_type' => 'required|array',
                    'starter_declaration' => 'nullable|array',
                    'tax_code' => 'required',
                    'tax_basis' => 'required',
                    'previous_taxable_salary' => 'nullable',
                    'previous_tax_paid' => 'nullable',
                    'current_employment_taxable_pay_ytd' => 'nullable',
                    'current_employment_tax_paid_ytd' => 'nullable',
                    'employee_pension_contributions_ytd' => 'nullable',
                    'payrolled_benefits_ytd' => 'nullable',
                    'employment_statutory_payments_loans' => 'nullable',

                    'ni_category' => 'nullable|array',
                    'gross_earnings_for_nic_ytd' => 'nullable',
                    'earnings_at_lel_ytd' => 'nullable',
                    'earnings_at_pt_ytd' => 'nullable',
                    'earnings_at_uel_ytd' => 'nullable',
                    'employee_nic_ytd' => 'nullable',
                    'employer_nic_ytd' => 'nullable',

                    'total_statutory_maternity_pay' => 'nullable',
                    'total_statutory_paternity_pay' => 'nullable',
                    'total_shared_parental_pay' => 'nullable',
                    'total_statutory_adoption_pay' => 'nullable',
                    'total_statutory_parental_bereavement_pay' => 'nullable',
                    'total_postgraduate_loan_deductions' => 'nullable',
                    'total_student_loan_deductions' => 'nullable',
                    'total_statutory_sick_pay' => 'nullable'
                ]);

                if ($validator->fails())
                {
                    return response()->json(['errors'=>$validator->errors()->all()], 422);
                }
                $starterDetail=new EmployeeStarterDetail();
                $starterDetail->employee_id=$employee_id;
                $starterDetail->starter_type=$request->starter_type['code'];
                $starterDetail->tax_code=$request->tax_code;
                $starterDetail->tax_basis=$request->tax_basis;
                $starterDetail->previous_taxable_salary=$request->previous_taxable_salary;
                $starterDetail->previous_tax_paid=$request->previous_tax_paid;
                $starterDetail->starter_declaration=$request->starter_declaration ? $request->starter_declaration['code']:null;
                $starterDetail->current_employment_taxable_pay_ytd=$request->current_employment_taxable_pay_ytd;
                $starterDetail->current_employment_tax_paid_ytd=$request->current_employment_tax_paid_ytd;
                $starterDetail->employee_pension_contributions_ytd=$request->employee_pension_contributions_ytd;
                $starterDetail->payrolled_benefits_ytd=$request->payrolled_benefits_ytd;
                $starterDetail->employment_statutory_payments_loans=$request->employment_statutory_payments_loans;
                $starterDetail->save();

                if($request->starter_type['code']=='Existing Employee'){
                $niCategory=new EmployeeNICategory();
                $niCategory->employee_id=$employee_id;
                $niCategory->ni_category=$request->ni_category ? $request->ni_category['code']:null;
                $niCategory->gross_earnings_for_nic_ytd=$request->gross_earnings_for_nic_ytd;
                $niCategory->earnings_at_lel_ytd=$request->earnings_at_lel_ytd;
                $niCategory->earnings_at_pt_ytd=$request->earnings_at_pt_ytd;
                $niCategory->earnings_at_uel_ytd=$request->earnings_at_uel_ytd;
                $niCategory->employee_nic_ytd=$request->employee_nic_ytd;
                $niCategory->employer_nic_ytd=$request->employer_nic_ytd;
                $niCategory->save();

                $statutoryPaymentsLoans=new EmploymentStatutoryPaymentsLoans();
                $statutoryPaymentsLoans->employee_id=$employee_id;
                $statutoryPaymentsLoans->total_statutory_maternity_pay=$request->total_statutory_maternity_pay;
                $statutoryPaymentsLoans->total_statutory_paternity_pay=$request->total_statutory_paternity_pay;
                $statutoryPaymentsLoans->total_shared_parental_pay=$request->total_shared_parental_pay;
                $statutoryPaymentsLoans->total_statutory_adoption_pay=$request->total_statutory_adoption_pay;
                $statutoryPaymentsLoans->total_statutory_parental_bereavement_pay=$request->total_statutory_parental_bereavement_pay;
                $statutoryPaymentsLoans->total_postgraduate_loan_deductions=$request->total_postgraduate_loan_deductions;
                $statutoryPaymentsLoans->total_statutory_sick_pay=$request->total_statutory_sick_pay;
                $statutoryPaymentsLoans->total_student_loan_deductions=$request->total_student_loan_deductions;
                $statutoryPaymentsLoans->save();
                }

                $employee->step=3;
                $employee->status='Active';
                $employee->save();
                $response['employee_id']=$employee_id;
                $response['message']='Successfully Saved';
                $response['step']=3;
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

    public function editEmployee($id){
        $employee=Employee::where('id',$id)->first();
        return response()->json($employee,200);
    }

    public function updateEmployee(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'title' => 'required|array',
            'forename' => 'required|string',
            'surname' => 'required|string|max:255',
            'gender' => 'required|array',
            'dob' => 'nullable|date',
            'work_email' => 'required|email',
            'telephone' => 'nullable|string|max:255',
            'ni_category' => 'required|array',
            'nino' => 'nullable|string',
            'postcode' => 'required|string|max:255',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string',
            'city' => 'required|string',
            'country' => 'required|string',
            'payroll_id' => 'required',
            'employement_start_date' => 'required|date',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $employee=Employee::find($id);
        $employee->title=$request->title['code'];
        $employee->forename=$request->forename;
        $employee->surname=$request->surname;
        $employee->gender=$request->gender['code'];
        $employee->dob=$request->dob;
        $employee->work_email=$request->work_email;
        $employee->telephone=$request->telephone;
        $employee->ni_category = $request->ni_category['code'];
        $employee->nino = $request->nino;
        $employee->postcode=$request->postcode;
        $employee->address_line1=$request->address_line1;
        $employee->address_line2=$request->address_line2;
        $employee->city = $request->city;
        $employee->country = $request->country;
        $employee->payroll_id=$request->payroll_id;
        $employee->employement_start_date=$request->employement_start_date;
        $employee->save();
        $response['message']='Successfully Updated';
        return response()->json($response,200);
    }

    public function deleteEmployee($id){
        $employee=Employee::find($id);
        $employee->delete();
        $response['message']='Successfully Deleted';
        return response()->json($response,200);
    }

    public function searchEmployee(Request $request){
        $validator = Validator::make($request->all(), [
            'searchTerm' => 'nullable',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }

        $searchTerm = $request->input('searchTerm');
        $employees = Employee::when($searchTerm, function ($query, $searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('forename', 'like', '%' . $searchTerm . '%')
                  ->orWhere('surname', 'like', '%' . $searchTerm . '%')
                  ->orWhere(DB::raw("CONCAT(forename, ' ', surname)"), 'like', '%' . $searchTerm . '%');
                //   ->orWhere('payroll_id', 'like', '%' . $searchTerm . '%');
            });
        })->get();    
        $employeeResources = EmployeeResource::collection($employees);
        return response()->json($employeeResources,200);
    }

    public function getEmployeePaySchedule($id){
        $employee = EmployementDetail::with(['paySchedule:id,name'])->where('employee_id', $id)
        ->first();      
        return response()->json($employee,200);
    }

    public function updateEmployeePaySchedule(Request $request){
        $validator = Validator::make($request->all(), [
            'pay_schedule_id' => 'required|array',
            'employee_id' => 'required',                            
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $employee = EmployementDetail::where('employee_id', $request->employee_id)->first(); 
        $employee->pay_schedule_id = $request->pay_schedule_id['code'];
        $employee->save();
        $response['message']='Successfully Updated Pay Schedule';
        return response()->json($response,200);
    }

    public function updateEmployeeSalary(Request $request,$id){
        $validator = Validator::make($request->all(), [
            'salary_type' => 'required|array',
            'anual_salary' => 'nullable', 
            'monthly_salary' => 'nullable',
            'weekly_salary' => 'nullable',  
            'hourly_equivalent' => 'required',
            'expected_work_hours_per_week' => 'required',                             
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $employee = EmployementDetail::where('employee_id', $id)->first(); 
        $employee->salary_type = $request->salary_type['code'];
        $employee->anual_salary = $request->anual_salary;
        $employee->monthly_salary = $request->monthly_salary;
        $employee->weekly_salary = $request->weekly_salary;
        $employee->hourly_equivalent = $request->hourly_equivalent;
        $employee->expected_work_hours_per_week = $request->expected_work_hours_per_week;
        $employee->save();

        $this->Payroll_Repository->update_payroll_calculations($id);
        
        $response['message']='Successfully Updated Salary';
        return response()->json($response,200);
    }

    public function getEmployeeTaxes($id){
        $employee = EmployeeStarterDetail::where('employee_id', $id)->first();      
        return response()->json($employee,200);
    }

    public function updateEmployeeTaxes(Request $request,$id){
        $validator = Validator::make($request->all(), [
            'starter_type' => 'required|array',
            'tax_basis' => 'required|array',  
            'tax_code' => 'required',  
            'previous_taxable_salary' => 'nullable',
            'previous_tax_paid' => 'nullable',
            'starter_declaration' => 'nullable|array'                                                    
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $starter_detail = EmployeeStarterDetail::where('employee_id', $id)->first(); 
        $starter_detail->starter_type = $request->starter_type['code'];
        $starter_detail->tax_basis = $request->tax_basis['code'];
        $starter_detail->tax_code = $request->tax_code;
        $starter_detail->previous_taxable_salary = $request->previous_taxable_salary;
        $starter_detail->previous_tax_paid = $request->previous_tax_paid;
        $starter_detail->starter_declaration = $request->starter_declaration['code'];
        $starter_detail->save();
        $response['message']='Successfully Updated Employee Taxes';
        return response()->json($response,200);
    }
    
    public function getEmployeePension($id){
        $pension = EmployeePension::where('employee_id', $id)->first();      
        return response()->json($pension,200);
    }

    public function updateEmployeePension(Request $request,$id){
        $validator = Validator::make($request->all(), [
            'pension_type' => 'required|array',
            'pension_calculation' => 'required|array',  
            'employee_contribution' => 'required',  
            'employer_contribution' => 'nullable',                                                 
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $pension = EmployeePension::where('employee_id', $id)->first(); 
        if($pension){

        }else{
            $pension = new EmployeePension(); 
            $pension->employee_id = $id;
        }
        $pension->pension_type = $request->pension_type['code'];
        $pension->pension_calculation = $request->pension_calculation['code'];
        $pension->employee_contribution = $request->employee_contribution;
        $pension->employer_contribution = $request->employer_contribution;
        $pension->save();
        $this->Payroll_Repository->update_payroll_calculations($id);
        $response['message']='Successfully Updated Employee Pension';
        return response()->json($response,200);
    }

    public function getPaternityLeave($id){
        $paternity = EmployeePaternityLeave::where('employee_id', $id)->first();      
        return response()->json($paternity,200);
    }

    public function updatePaternityLeave(Request $request,$id){
        $validator = Validator::make($request->all(), [
            'leave_type' => 'required|array',
            'expected_due_date' => 'required',  
            'start_date' => 'required',  
            'end_date' => 'required',  
            'second_block_start_date' => 'nullable',  
            'second_block_end_date' => 'nullable',          
            'average_weekly_earnings' => 'required',                                                                                          
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $paternity = EmployeePaternityLeave::where('employee_id', $id)->first(); 
        if($paternity){

        }else{
            $paternity = new EmployeePaternityLeave(); 
            $paternity->employee_id = $id;
        }
        $paternity->leave_type = $request->leave_type['code'];
        $paternity->expected_due_date = $request->expected_due_date;
        $paternity->start_date = $request->start_date;
        $paternity->end_date = $request->end_date;
        $paternity->second_block_start_date = $request->second_block_start_date;
        $paternity->second_block_end_date = $request->second_block_end_date;
        $paternity->average_weekly_earnings = $request->average_weekly_earnings;
        $paternity->save();
        $this->Payroll_Repository->update_paternitypay_calculations($id);
        $response['message']='Successfully Updated Employee Paternity Leave';
        return response()->json($response,200);
    }
}
