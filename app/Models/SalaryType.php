<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryType extends Model
{
    use HasFactory;

    protected $casts = [
        'salary_rate' => 'decimal:2',
    ];
}
