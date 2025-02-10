<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FPSSubmissionResource extends JsonResource
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
            'pay_run_start_date' => $this->pay_run_start_date,
            'pay_run_end_date' => $this->pay_run_end_date,
            'tax_period' => $this->tax_period,
            'status' => $this->status,
            'submission_xml' => $this->submission_xml,
            'response_xml' => $this->response_xml,
            'payschedule_name' => $this->payroll ? $this->payroll->payschedule->name:'',
            'submission_date' => $this->submission_date,
            'created_at' => $this->created_at,
        ];
    }
}
