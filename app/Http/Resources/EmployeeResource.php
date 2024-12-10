<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'name' => $this->forename.' '.$this->surname,
            'payroll_id' => $this->payroll_id,
            'payroll_status' => $this->status,
            'pension_status' => 'Not enrolled',
            'salary' => 1500,
            'start_date' => $this->employement_start_date,
            'step' => $this->step,
            'created_at' => $this->created_at,
        ];
    }
}
