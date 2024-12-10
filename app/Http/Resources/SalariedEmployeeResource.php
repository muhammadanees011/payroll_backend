<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalariedEmployeeResource extends JsonResource
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
            'pay_frequency' => $this->payschedule->pay_frequency,
            'hourly_equivalent' => $this->hourly_equivalent,
            'expected_work_hours_per_week' => $this->expected_work_hours_per_week,
            'anual_salary' => $this->anual_salary,
            'monthly_salary' => $this->monthly_salary,
            'weekly_salary' => $this->weekly_salary,
            'hours_worked' => $this->employee->payrollEmployee ? $this->employee->payrollEmployee->hours_worked : 0,
        ];
    }
}
