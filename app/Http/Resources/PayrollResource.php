<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollResource extends JsonResource
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
            'pay_schedule_id' => $this->payschedule->id,
            'pay_schedule_name' => $this->payschedule->name,
            'tax_period' => $this->tax_period,
            'pay_run_start_date' => $this->pay_run_start_date,
            'pay_run_end_date' => $this->pay_run_end_date,
            'pay_date' => $this->pay_date,
            'status' => $this->status,
        ];
    }
}
