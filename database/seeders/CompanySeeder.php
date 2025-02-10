<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::factory()->create([
            'name' => 'XEPOS Ltd',
            'legal_structure' => 'LTD-Private limited company',
            'post_code' => 'B28 9HH',
            'address_line_1' => 'Centre Court 1301 Stratford rd Shir',
            'address_line_2' => 'Centre Court 1301 Stratford rd Shir',
            'city' => 'Shirley',
            'country' => null,
            'registration_number' => null,
            'holiday_year_start_month' => null,
            'directory_name' => 'Amir',
            'authorized_to_act' => true,
            'agreed_to_terms' => true,
            'company_payee' => ["planning_to_pay_myself","planning_to_pay_employees"],
            'first_payday' => '2024-10-02',
            'is_first_payday_of_year' => true,
            'is_first_payroll_of_company' => null,
            'payroll_provider' => null,
            'step' => 4,
        ]);
    }
}
