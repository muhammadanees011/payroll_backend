<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SalaryType;

class SalaryTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SalaryType::factory()->create([
            'description' => "Junior Developer's Salary",
            'pensionable' => true,
            'code' => '32123',
            'salary_period' => 'hour',
            'salary_rate' => 20,
        ]);

        SalaryType::factory()->create([
            'description' => "Overtime Pay",
            'pensionable' => true,
            'code' => '32487',
            'salary_period' => 'unit',
            'salary_rate' => 30,
        ]);
        
    }
}
