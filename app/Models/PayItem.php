<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayItem extends Model
{
    use HasFactory;

    protected $casts = [
        'taxable' => 'boolean',
        'subject_to_national_insurance' => 'boolean',
        'pensionable' => 'boolean',
    ];
}
