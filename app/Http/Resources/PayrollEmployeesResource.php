<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollEmployeesResource extends JsonResource
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
            'gross_pay' => $this->gross_pay,
            'net_pay' => $this->net_pay,
            'pay_frequency' => $this->payschedule->pay_frequency,
            'hourly_equivalent' => $this->employementdetail->hourly_equivalent,
            'anual_salary' => $this->employementdetail->anual_salary,
            'monthly_salary' => $this->employementdetail->monthly_salary,
            'weekly_salary' => $this->employementdetail->weekly_salary,
            // 'pay_date' => $this->payroll->pay_date,
        ];
    }
}
