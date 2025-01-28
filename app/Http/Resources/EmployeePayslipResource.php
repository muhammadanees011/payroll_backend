<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeePayslipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee->id,
            'pay_schedule_id' => $this->pay_schedule_id,
            'name' => $this->employee->forename.' '.$this->employee->surname,
            'employee_payrollId' => $this->employee->payroll_id,
            'employee_nino' => $this->employee->nino,
            'employee_address_line1' => $this->employee->address_line1,
            'employee_address_line2' => $this->employee->address_line2,
            'employee_city' => $this->employee->city,
            'employee_postcode' => $this->employee->postcode,
            'tax_code' => $this->employeestarterdetail->tax_code,
            'ni_category' => $this->employee->ni_category,
            'gross_pay' => $this->gross_pay,
            'net_pay' => $this->net_pay,
            'base_pay' => $this->base_pay,
            'paye_income_tax' => $this->paye_income_tax,
            'employee_pension' => $this->employee_pension,
            'employee_nic' => $this->employee_nic,
            'employer_pension' => $this->employer_pension,
            'employer_nic' => $this->employer_nic,
            'pay_frequency' => $this->payschedule->pay_frequency,
            'hourly_equivalent' => $this->employementdetail->hourly_equivalent,
            'tax_period' => $this->payroll->tax_period,
            'pay_run_start_date' => $this->payroll->pay_run_start_date,
            'pay_run_end_date' => $this->payroll->pay_run_end_date,
            'pay_date' => $this->payroll->pay_date,
            'student_loan' => $this->student_loan,
            'pg_loan' => $this->pg_loan,
            'sick_pay' => $this->sick_pay,
            'paternity_pay' => $this->paternity_pay,
            'payitems' => $this->whenLoaded('employeePayItems'),
            'company' => $this->whenLoaded('company'),
            'yearlyEarnings' => $this->whenLoaded('yearlyEarnings'),
        ];
    }
}
