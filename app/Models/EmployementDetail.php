<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployementDetail extends Model
{
    use HasFactory;

    public function employee(){
        return $this->hasOne(Employee::class,'id','employee_id');
    }

    public function paySchedule(){
        return $this->hasOne(PaySchedule::class,'id','pay_schedule_id');
    }
}
