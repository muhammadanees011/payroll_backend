<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FPSSubmission extends Model
{
    use HasFactory;

    public function payroll(){
        return $this->hasOne(Payroll::class,'id','payroll_id');
    }
}
