<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LoanPaymentPlan;

class StudentLoanPaymentPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentPlans = [
            [
                'payment_plan' => 'Student Loan Repayment - Plan 1',
                'plan_description' => 'For students who started undergraduate studies in the UK before 1 September 2012',
                'annual_threshold' => 24990.00,
                'repay_percentage' => 9
            ],
            [
                'payment_plan' => 'Student Loan Repayment - Plan 2',
                'plan_description' => 'For students who started undergraduate studies in the UK after 1 September 2012',
                'annual_threshold' => 27295.00,
                'repay_percentage' => 9
            ],
            [
                'payment_plan' => 'Student Loan Repayment - Postgraduate',
                'plan_description' => 'For students who started a Postgraduate degree on or after 1 August 2016 or à Doctoral course on or after 1 August 2018',
                'annual_threshold' => 21000.00,
                'repay_percentage' => 6
            ],
            [
                'payment_plan' => 'Student Loan Repayment - Plan 4',
                'plan_description' => 'For students who started undergraduate studies in the UK after 1 September 2012',
                'annual_threshold' => 31395.00,
                'repay_percentage' => 9
            ]
        ];


        foreach ($paymentPlans as $plan) {
            LoanPaymentPlan::create($plan);
        }
    }
}
