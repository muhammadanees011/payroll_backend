<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    public function employmentDetail(){
        return $this->hasOne(EmployementDetail::class,'employee_id','id');
    }

    public function payrollEmployee(){
        return $this->hasOne(PayrollEmployee::class,'employee_id','id');
    }
}
