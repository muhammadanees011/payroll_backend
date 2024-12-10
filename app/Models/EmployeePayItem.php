<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePayItem extends Model
{
    use HasFactory;

    public function employee(){
        return $this->hasOne(Employee::class,'id','employee_id');
    }

    public function payroll(){
        return $this->hasOne(Payroll::class,'id','payroll_id');
    }

    public function payitem(){
        return $this->hasOne(PayItem::class,'id','pay_item_id');
    }

    public function salarytype(){
        return $this->hasOne(SalaryType::class,'id','salary_type_id');
    }

}
