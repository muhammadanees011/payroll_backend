<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentLoan extends Model
{
    use HasFactory;


    public function paymentplan(){
        return $this->hasOne(LoanPaymentPlan::class,'id','loan_payment_plan_id');
    }
}
