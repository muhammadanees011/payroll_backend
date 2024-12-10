<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HourlyEmployeeResource extends JsonResource
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
            'name' => $this->employee->forename.' '.$this->employee->surname,
            'employee_payrollId' => $this->employee->payroll_id,
            'gross_pay' => $this->employee->payrollEmployee ? $this->employee->payrollEmployee->gross_pay : 0,
            'net_pay' => $this->employee->payrollEmployee ? $this->employee->payrollEmployee->gross_pay : 0,
        ];
    }
}
