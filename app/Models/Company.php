<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $casts = [
        'authorized_to_act' => 'boolean',
        'agreed_to_terms' => 'boolean',
        'is_first_payday_of_year' => 'boolean',
    ];
}
