<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'gross_pay' => $this->gross_pay,
            'net_pay' => $this->net_pay,
            'base_pay' => $this->base_pay,
            'paye_income_tax' => $this->paye_income_tax,
            'employee_pension' => $this->employee_pension,
            'employee_nic' => $this->employee_nic,
            'employer_pension' => $this->employer_pension,
            'employer_nic' => $this->employer_nic,
            'student_loan' => $this->student_loan,
            'pg_loan' => $this->pg_loan,
            'employees_count' => $this->employees_count,
            // 'tax_period' => $this->tax_period,
            // 'pay_date' => $this->pay_date,
            'gross_addition' => $this->gross_addition_sum,
            'gross_deduction' => $this->gross_deduction_sum,
            'net_addition' => $this->net_addition_sum,
            'net_deduction' => $this->net_deduction_sum,
        ];
    }
}
