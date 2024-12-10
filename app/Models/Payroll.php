<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    public function payschedule(){
        return $this->hasOne(PaySchedule::class,'id','pay_schedule_id');
    }
}
