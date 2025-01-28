<?php

namespace App\Repositories;
use App\Models\StudentLoan;
use App\Models\PayrollEmployee;
use App\Models\EmployementDetail;
use App\Models\EmployeePension;
use App\Models\EmployeeSickLeave;
use App\Models\EmployeePaternityLeave;
use App\Repositories\Interfaces\Payroll_Interface;
use App\Services\NICCalculator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class Payroll_Repository implements Payroll_Interface {

    protected $nicCalculator;

    public function __construct(NICCalculator $nicCalculator)
    {
        $this->nicCalculator = $nicCalculator;
    }

    //------------CALCULATE AND SET PG/STUDENT LOAN REPAYMENTS----------------
    public function set_loan_repayments($employee_id,$pay_frequancy, $employeePayroll){
        $studentPlans=StudentLoan::with('paymentplan')->where('employee_id',$employee_id)->get();

        foreach($studentPlans as $plan){
            $plan=$plan->paymentplan;
            $anual_salary=$employeePayroll->base_pay*12;
            if($anual_salary > $plan->annual_threshold){

                $repayment_amount=0;
                if($pay_frequancy=='monthly'){
                    $incomeAboveThreshold=$anual_salary - $plan->annual_threshold;
                    $repayment_rate=$plan->repay_percentage/100;
                    $repayment_amount=$incomeAboveThreshold*$repayment_rate;
                    $repayment_amount=$repayment_amount/12;
                }elseif($pay_frequancy=='weekly'){
                    $incomeAboveThreshold=$anual_salary - $plan->annual_threshold;
                    $repayment_rate=$plan->repay_percentage/100;
                    $repayment_amount=$incomeAboveThreshold*$repayment_rate;
                    $repayment_amount=$repayment_amount/52;
                }elseif($pay_frequancy=='fortnightly'){
                    $incomeAboveThreshold=$anual_salary - $plan->annual_threshold;
                    $repayment_rate=$plan->repay_percentage/100;
                    $repayment_amount=$incomeAboveThreshold*$repayment_rate;
                    $repayment_amount=$repayment_amount/26;
                }elseif($pay_frequancy=='fourweekly'){
                    $incomeAboveThreshold=$anual_salary - $plan->annual_threshold;
                    $repayment_rate=$plan->repay_percentage/100;
                    $repayment_amount=$incomeAboveThreshold*$repayment_rate;
                    $repayment_amount=$repayment_amount/13;

                }

                if($plan->type=='pg_loan'){
                    if($employeePayroll->pg_loan==null){
                        $employeePayroll->pg_loan=$repayment_amount;
                        $employeePayroll->net_pay=$employeePayroll->net_pay - $repayment_amount;
                    }
                }else{
                    if($employeePayroll->student_loan==null){
                        $employeePayroll->student_loan=$repayment_amount; 
                        $employeePayroll->net_pay=$employeePayroll->net_pay - $repayment_amount; 
                    }
                }
            }
        }

    }
    
    //------------UPDATE PAYROLL CALCULATIONS AFTER SALARY UPDATED----------------
    public function update_payroll_calculations($employee_id, $type=null){
        $payroll=PayrollEmployee::with('paySchedule')->where('employee_id',$employee_id)
        ->where('status','active')->first();
        if($payroll){
            $employeeDetail = EmployementDetail::where('employee_id', $employee_id)->first(); 
            $employeePension = EmployeePension::where('employee_id', $employee_id)->first();
            
            if ($employeeDetail->salary_type == 'Hourly') {
                $payroll->salary_type = 'Hourly';
                $payroll->base_pay  = $payroll->hours_worked * $employeeDetail->hourly_equivalent;
                // $payroll->gross_pay = $payroll->hours_worked * $employeeDetail->hourly_equivalent;
                $payroll->gross_pay = $payroll->gross_pay - $this->deductSickDaysSalary($employee_id);
                if($type==null){
                    $payroll->gross_pay = $payroll->gross_pay + ($payroll->sick_pay ? $payroll->sick_pay: 0);
                    $payroll->gross_pay = $payroll->gross_pay + ($payroll->paternity_pay ? $payroll->paternity_pay: 0);
                }
                $payroll->hourly_rate = $employeeDetail->hourly_equivalent;
                $nic_data=$this->nicCalculator->calculateNIC($payroll->gross_pay);
                $payroll->employee_nic=$nic_data['employee_nic'];
                $payroll->employer_nic=$nic_data['employer_nic'];
                $payroll->employee_pension=$employeePension ? ($payroll->gross_pay) * ($employeePension->employee_contribution / 100):0;
                $payroll->employer_pension=$employeePension ? ($payroll->gross_pay) * ($employeePension->employer_contribution / 100):0;
                if($payroll->paySchedule->pay_frequency=='Monthly'){
                    $this->set_loan_repayments($payroll->employee_id,'monthly', $payroll);
                }else if($payroll->paySchedule->pay_frequency=='Weekly'){
                    $this->set_loan_repayments($payroll->employee_id,'weekly', $payroll);
                }
                $income_tax = $this->nicCalculator->calculatePAYE($payroll->gross_pay, $employeeDetail->pay_frequency, '1257L');
                $payroll->paye_income_tax=$income_tax;
                $payroll->net_pay=($payroll->gross_pay)-($payroll->pg_loan + $payroll->student_loan + $payroll->paye_income_tax + $payroll->employee_pension + $payroll->employee_nic);
                if($type==null){
                    $payroll->gross_pay = $payroll->gross_pay + ($payroll->sick_pay ? $payroll->sick_pay: 0);
                    $payroll->gross_pay = $payroll->gross_pay + ($payroll->paternity_pay ? $payroll->paternity_pay: 0);
                }
            }elseif ($employeeDetail->salary_type == 'Salaried') {
                if($payroll->paySchedule->pay_frequency=='Monthly'){
                    // $payroll->gross_pay = $employeeDetail->monthly_salary;
                    $payroll->gross_pay = $payroll->gross_pay - $this->deductSickDaysSalary($employee_id);
                    if($type==null){
                        $payroll->gross_pay = $payroll->gross_pay + ($payroll->sick_pay ? $payroll->sick_pay: 0);
                        $payroll->gross_pay = $payroll->gross_pay + ($payroll->paternity_pay ? $payroll->paternity_pay: 0);
                    }
                    $payroll->net_pay = $employeeDetail->monthly_salary;
                    $payroll->base_pay = $employeeDetail->monthly_salary;
                    $this->set_loan_repayments($payroll->employee_id,'monthly', $payroll);
                }else if($payroll->paySchedule->pay_frequency=='Weekly'){
                    // $payroll->gross_pay = $employeeDetail->weekly_salary;
                    $payroll->gross_pay = $payroll->gross_pay - $this->deductSickDaysSalary($employee_id);
                    if($type==null){
                        $payroll->gross_pay = $payroll->gross_pay + ($payroll->sick_pay ? $payroll->sick_pay: 0);
                        $payroll->gross_pay = $payroll->gross_pay + ($payroll->paternity_pay ? $payroll->paternity_pay: 0);
                    }
                    $payroll->net_pay = $employeeDetail->weekly_salary;
                    $payroll->base_pay = $employeeDetail->weekly_salary;
                    $this->set_loan_repayments($payroll->employee_id,'weekly', $payroll);
                }
                $payroll->salary_type = 'Salaried';
                $nic_data = $this->nicCalculator->calculateNIC($payroll->gross_pay);
                $payroll->employee_nic = $nic_data['employee_nic'];
                $payroll->employer_nic = $nic_data['employer_nic'];
                $payroll->employee_pension = $employeePension ? ($payroll->gross_pay) * ($employeePension->employee_contribution / 100):0;
                $payroll->employer_pension = $employeePension ? ($payroll->gross_pay) * ($employeePension->employer_contribution / 100):0;
                $income_tax = $this->nicCalculator->calculatePAYE($payroll->gross_pay, $payroll->paySchedule->pay_frequency, '1257L');
                $payroll->paye_income_tax = $income_tax;
                $payroll->net_pay = ($payroll->gross_pay)-($payroll->pg_loan + $payroll->student_loan + $payroll->paye_income_tax + $payroll->employee_pension + $payroll->employee_nic);
                // $payroll->net_pay = $payroll->net_pay + ($payroll->sick_pay ? $payroll->sick_pay: 0);
                // $payroll->net_pay = $payroll->net_pay + ($payroll->paternity_pay ? $payroll->paternity_pay: 0);
            }
            $payroll->save();
        }
    }

    //------------UPDATE SICKLEAVE CALCULATIONS----------------
    public function update_sickleave_calculations($employee_id){

        $payroll=PayrollEmployee::with('paySchedule','payroll')->where('employee_id',$employee_id)
        ->where('status','active')->first();

        $sickLeaves=EmployeeSickLeave::where('employee_id',$employee_id)
        ->where('status','pending')
        ->whereNotNull('end_date')->get();

        $deductable_days=0;
        $payable_days=0;
        $weekend_days=0;
        foreach($sickLeaves as $leave){
            
            $sickLeaveEndDate = Carbon::parse($leave->end_date);
            $payrunStartDate = Carbon::parse($payroll->payroll->pay_run_start_date);
            $payrunEndDate = Carbon::parse($payroll->payroll->pay_run_end_date);
    
            if ($sickLeaveEndDate->between($payrunStartDate, $payrunEndDate)) {
            }else{
                continue;
            }

            $workingDays = $this->countWorkingDays($leave->start_date, $leave->end_date); //count working days
            $weekend_days = $leave->days_unavailable - $workingDays; // set weekend days
            $deductable_days=$deductable_days + $workingDays;
            $waiting_days = $leave->statutory_waiting_days;
            $payable_days=$payable_days + $leave->statutory_payable_days;
            $leave->status = 'processed';
            $leave->save();
        }

        $workingDays=$this->getWorkingDays($payroll->payroll->pay_run_start_date, $payroll->payroll->pay_run_end_date);
        $per_day_salary=$payroll->base_pay/$workingDays;
        $payroll->gross_pay=$payroll->gross_pay - ($per_day_salary * $deductable_days);
        $payroll->sick_pay= ($payable_days * 23.35);
        // $payroll->net_pay=$payroll->net_pay + ($payable_days * 23.35);
        // $payroll->gross_pay=$payroll->gross_pay + ($payable_days * 23.35);
        $payroll->save();
        $this->update_payroll_calculations($employee_id,'leaves');
    }

    public function update_paternitypay_calculations($employee_id){
        $payroll=PayrollEmployee::with('employementdetail','paySchedule','payroll')->where('employee_id',$employee_id)
        ->where('status','active')->first();

        $paternityLeave=EmployeePaternityLeave::where('employee_id',$employee_id)
        ->where('status','pending')->first();

        if (!$payroll || !$paternityLeave) {
            return;
        }

        if($payroll->paternity_pay){
            $payroll->net_pay = $payroll->net_pay - $payroll->paternity_pay;
            $payroll->save();
        }

        $paternityLeaveEndDate = Carbon::parse($paternityLeave->end_date);
        $paternityLeaveSecondEndDate = Carbon::parse($paternityLeave->second_block_end_date);
        $payrunStartDate = Carbon::parse($payroll->payroll->pay_run_start_date);
        $payrunEndDate = Carbon::parse($payroll->payroll->pay_run_end_date);

        $isSecondBlock=false;
        $isFirstBlock=false;
        if ($paternityLeaveEndDate->between($payrunStartDate, $payrunEndDate)) {
            $isFirstBlock=true;
            if ($paternityLeaveSecondEndDate->between($payrunStartDate, $payrunEndDate)) {
                $isSecondBlock=true;
            }
        } else {
            if ($paternityLeaveSecondEndDate->between($payrunStartDate, $payrunEndDate)) {
                $isSecondBlock=true;
            }
        }

        if(!$isFirstBlock && !$isSecondBlock){
            return;
        }

        $deductable_days=0;
        $payable_days=0;
        $workingDays=0;

        if($isFirstBlock){
            $workingDays = $this->countWorkingDays($paternityLeave->start_date, $paternityLeave->end_date); //count working days
            $deductable_days=$deductable_days + $workingDays;
            $startDate = Carbon::parse($paternityLeave->start_date);
            $endDate = Carbon::parse($paternityLeave->end_date);
            $payable_days = $payable_days + $startDate->diffInDays($endDate); 
        }
        if($isSecondBlock){
            $workingDays = $this->countWorkingDays($paternityLeave->second_block_start_date, $paternityLeave->second_block_end_date); //count working days
            $deductable_days=$deductable_days + $workingDays;
            $startDate = Carbon::parse($paternityLeave->second_block_start_date);
            $endDate = Carbon::parse($paternityLeave->second_block_end_date);
            $payable_days = $payable_days + $startDate->diffInDays($endDate); 
        }

        $per_day_salary=$payroll->employementdetail->weekly_pay/5;
        $hmrc_weekly_rate=184.03;
        $salary_weekly_rate=$payroll->employementdetail->weekly_salary * 0.9;
        $weeklyRate = min($hmrc_weekly_rate, $salary_weekly_rate);
        
        if($isFirstBlock && $isSecondBlock){
            $weeklyRate=$weeklyRate * 2;
        }else if($isFirstBlock && $paternityLeave->leave_type=='My employee will take 2 weeks of leave in a row'){
            $weeklyRate=$weeklyRate * 2;
        }

        $payroll->gross_pay=$payroll->gross_pay - ($per_day_salary * $deductable_days);
        $payroll->paternity_pay= $weeklyRate;
        // $payroll->net_pay=$payroll->net_pay + $weeklyRate;
        // $payroll->gross_pay=$payroll->gross_pay + $weeklyRate;
        $payroll->save();
        $this->update_payroll_calculations($employee_id,'leaves');
    }

    function countWorkingDays($startDate, $endDate)
    {
        // Parse the dates
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Generate a period between the dates
        $period = CarbonPeriod::create($start, $end);

        // Count weekdays (Monday to Friday)
        $workingDays = 0;
        foreach ($period as $date) {
            if ($date->isWeekday()) { // Excludes Saturday and Sunday
                $workingDays++;
            }
        }

        return $workingDays;
    }


    function getWorkingDays($startDate, $endDate)
    {
        // Convert the provided dates to Carbon instances
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        // Generate the date range
        $period = CarbonPeriod::create($start, $end);
        $workingDays = 0;
        // Loop through each day in the period
        foreach ($period as $date) {
            // Count only weekdays (Monday–Friday)
            if ($date->isWeekday()) {
                $workingDays++;
            }
        }
        return $workingDays;
    }


    function deductSickDaysSalary($employee_id){
        $payroll=PayrollEmployee::with('paySchedule','payroll')->where('employee_id',$employee_id)
        ->where('status','active')->first();

        $sickLeaves=EmployeeSickLeave::where('employee_id',$employee_id)
        ->where('status','pending')
        ->whereNotNull('end_date')->get();

        $deductable_days=0;
        $payable_days=0;
        $weekend_days=0;
        foreach($sickLeaves as $leave){
            
            $sickLeaveEndDate = Carbon::parse($leave->end_date);
            $payrunStartDate = Carbon::parse($payroll->payroll->pay_run_start_date);
            $payrunEndDate = Carbon::parse($payroll->payroll->pay_run_end_date);
    
            if ($sickLeaveEndDate->between($payrunStartDate, $payrunEndDate)) {
            }else{
                continue;
            }

            $workingDays = $this->countWorkingDays($leave->start_date, $leave->end_date); //count working days
            $weekend_days = $leave->days_unavailable - $workingDays; // set weekend days
            $deductable_days=$deductable_days + $workingDays;
            $waiting_days = $leave->statutory_waiting_days;
            $payable_days=$payable_days + $leave->statutory_payable_days;
        }

        $workingDays=$this->getWorkingDays($payroll->payroll->pay_run_start_date, $payroll->payroll->pay_run_end_date);
        $per_day_salary=$payroll->base_pay/$workingDays;

        return $per_day_salary * $deductable_days;
    }

}