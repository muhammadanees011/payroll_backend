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
                'monthly_threshold' => 24990.00/12,
                'weekly_threshold' => 24990.00/52,
                'fortnightly_threshold' => 24990.00/26,
                'fourweekly_threshold' => 24990.00/13,
                'repay_percentage' => 9,
                'type' => 'student_loan'
            ],
            [
                'payment_plan' => 'Student Loan Repayment - Plan 2',
                'plan_description' => 'For students who started undergraduate studies in the UK after 1 September 2012',
                'annual_threshold' => 27295.00,
                'monthly_threshold' => 27295.00/12,
                'weekly_threshold' => 27295.00/52,
                'fortnightly_threshold' => 27295.00/26,
                'fourweekly_threshold' => 27295.00/13,
                'repay_percentage' => 9,
                'type' => 'student_loan'
            ],
            [
                'payment_plan' => 'Student Loan Repayment - Postgraduate',
                'plan_description' => 'For students who started a Postgraduate degree on or after 1 August 2016 or à Doctoral course on or after 1 August 2018',
                'annual_threshold' => 21000.00,
                'monthly_threshold' => 21000.00/12,
                'weekly_threshold' => 21000.00/52,
                'fortnightly_threshold' => 21000.00/26,
                'fourweekly_threshold' => 21000.00/13,
                'repay_percentage' => 6,
                'type' => 'pg_loan'
            ],
            [
                'payment_plan' => 'Student Loan Repayment - Plan 4',
                'plan_description' => 'For students who started undergraduate studies in the UK after 1 September 2012',
                'annual_threshold' => 31395.00,
                'monthly_threshold' => 31395.00/12,
                'weekly_threshold' => 31395.00/52,
                'fortnightly_threshold' => 31395.00/26,
                'fourweekly_threshold' => 31395.00/13,
                'repay_percentage' => 9,
                'type' => 'student_loan'
            ]
        ];


        foreach ($paymentPlans as $plan) {
            LoanPaymentPlan::create($plan);
        }
    }
}
