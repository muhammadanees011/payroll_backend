<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class P32TaxesFilingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxYear = '24-25'; // Representing 2024-2025 as an integer

        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $data[] = [
                'tax_month' => $month,
                'tax_year' => $taxYear,
                'total_paye' => 0.00,
                'gross_national_insurance' => 0.00,
                'claimed_employment_allowance' => 0.00,
                'total_statutory_recoveries' => 0.00,
                'apprentice_levy' => 0.00,
                'cis_deductions' => 0.00,
                'amount_due' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('p32_taxes_filings')->insert($data);
    }
}
