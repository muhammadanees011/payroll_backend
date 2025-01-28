<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaySchedule extends Model
{
    use HasFactory;

    public function payScheduleEmployees()
    {
        return $this->hasMany(EmployementDetail::class, 'pay_schedule_id', 'id');
    }
}
