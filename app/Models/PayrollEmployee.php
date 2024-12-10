<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollEmployee extends Model
{
    use HasFactory;

    public function employee(){
        return $this->hasOne(Employee::class,'id','employee_id');
    }

    public function paySchedule(){
        return $this->hasOne(PaySchedule::class,'id','pay_schedule_id');
    }

    public function employementdetail(){
        return $this->hasOne(EmployementDetail::class,'employee_id','employee_id');
    }

    public function payroll(){
        return $this->hasOne(Payroll::class,'id','payroll_id');
    }

    public function employeestarterdetail(){
        return $this->hasOne(EmployeeStarterDetail::class,'employee_id','employee_id');
    }
}
