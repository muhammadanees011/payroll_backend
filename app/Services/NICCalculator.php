<?php

namespace App\Services;
use App\Models\Company;

class NICCalculator
{
    protected $monthlyThresholds;
    protected $weeklyThresholds;

    public function __construct()
    {
        // Set thresholds for 2024/25
        $this->monthlyThresholds = [
            'primary' => 1048,    // Monthly Primary Threshold (PT)
            'upper' => 4189,      // Monthly Upper Earnings Limit (UEL)
            'secondary' => 758,   // Monthly Secondary Threshold (ST)
            'apprentice' => 4189, // Monthly Apprentice Upper Secondary Threshold (AUST)
        ];
        $this->weeklyThresholds = [
            'primary' => 242,     // Weekly PT
            'upper' => 967,       // Weekly UEL
            'secondary' => 175,   // Weekly ST
            'apprentice' => 967,  // Weekly AUST
        ];
    }

    protected function getThresholds($frequency)
    {
        return $frequency === 'Weekly' ? $this->weeklyThresholds : $this->monthlyThresholds;
    }

    /**
     * Calculate NIC for all scenarios based on category letter.
     */
    public function calculateNIC($grossEarnings, $frequency = 'Monthly', $category = 'A')
    {
        $thresholds = $this->getThresholds($frequency);

        // Employee NIC
        $employeeNIC = $this->calculateEmployeeNIC($grossEarnings, $thresholds, $category);

        // Employer NIC
        $employerNIC = $this->calculateEmployerNIC($grossEarnings, $thresholds, $category);

        return [
            'employee_nic' => round($employeeNIC, 2),
            'employer_nic' => round($employerNIC, 2),
        ];
    }

    /**
     * Calculate Employee NIC based on category letter.
     */
    protected function calculateEmployeeNIC($grossEarnings, $thresholds, $category)
    {
        if ($category === 'C') {
            // No employee NICs for Category C (State Pension age)
            return 0;
        }

        $pt = $thresholds['primary'];
        $uel = $thresholds['upper'];
        $st = $thresholds['secondary'];
        $aust = $thresholds['apprentice'];

        if ($grossEarnings <= $pt) {
            return 0;
        } elseif ($grossEarnings <= $uel) {
            return ($grossEarnings - $pt) * 0.12;
        } else {
            return ($uel - $pt) * 0.12 + ($grossEarnings - $uel) * 0.02;
        }
    }

    /**
     * Calculate Employer NIC based on category letter.
     */
    protected function calculateEmployerNIC($grossEarnings, $thresholds, $category)
    {
        $st = $thresholds['secondary'];
        $aust = $thresholds['apprentice'];

        switch ($category) {
            case 'A': // Standard rate
                return $grossEarnings > $st ? ($grossEarnings - $st) * 0.138 : 0;

            case 'H': // Apprentices under 25
            case 'M': // Employees under 21
            case 'Z': // Employees under 21, reduced employer NIC
                return $grossEarnings > $aust ? ($grossEarnings - $aust) * 0.138 : 0;

            case 'C': // State Pension age
                return $grossEarnings > $st ? ($grossEarnings - $st) * 0.138 : 0;

            default:
                throw new \InvalidArgumentException("Unknown NIC category: $category");
        }
    }


    /**
     * Calculate PAYE Income Tax.
     */
    public function calculatePAYE($salary, $frequency = 'Monthly', $taxCode = '1257L')
    {
        // Convert salary to annual equivalent based on frequency
        $annualSalary = $frequency === 'Weekly' ? $salary * 52 : $salary * 12;

        $personalAllowance = intval(substr($taxCode, 0, -1)) * 10;
        $basicRateLimit = 50270;
        $higherRateLimit = 125140;
        $basicRate = 0.20;
        $higherRate = 0.40;
        $additionalRate = 0.45;

        if ($annualSalary > 100000) {
            $personalAllowance -= ($annualSalary - 100000) / 2;
            $personalAllowance = max(0, $personalAllowance);
        }

        $taxableIncome = max(0, $annualSalary - $personalAllowance);
        $tax = 0;

        if ($taxableIncome > $higherRateLimit) {
            $tax += ($taxableIncome - $higherRateLimit) * $additionalRate;
            $taxableIncome = $higherRateLimit;
        }
        if ($taxableIncome > $basicRateLimit) {
            $tax += ($taxableIncome - $basicRateLimit) * $higherRate;
            $taxableIncome = $basicRateLimit;
        }
        $tax += $taxableIncome * $basicRate;

        // Convert tax to the selected frequency
        return $frequency === 'Weekly' ? $tax / 52 : $tax / 12;
    }


    //Calculate Lower Earning Limit (LEL)
    public function calculateLEL($grosspay,$payfrequency){
        $annual_LEL = 6396;
        $Monthly_LEL = $annual_LEL/12;
        $Weekly_LEL = $annual_LEL/52;
        $Fortnightly_LEL = $annual_LEL/26;
        $FourWeekly_LEL = $annual_LEL/13;

        if($payfrequency == 'Monthly' && $grosspay >= $Monthly_LEL ){
            return $Monthly_LEL;
        }else if($payfrequency == 'Weekly' && $grosspay >= $Weekly_LEL){
            return $Weekly_LEL;
        }else if($payfrequency == 'Fortnightly' && $grosspay >= $Fortnightly_LEL){
            return $Fortnightly_LEL;
        }else if($payfrequency == 'Four Weekly' && $grosspay >= $FourWeekly_LEL){
            return $FourWeekly_LEL;
        }else{
            return 0;
        }
    }

    //Calculate Primary Threshold (PT)
    public function calculatePT($grosspay,$payfrequency){
        $annual_PT = 12570;
        $Monthly_PT = $annual_PT/12;
        $Weekly_PT = $annual_PT/52;
        $Fortnightly_PT = $annual_PT/26;
        $FourWeekly_PT = $annual_PT/13;

        if($payfrequency == 'Monthly' && $grosspay >= $Monthly_PT ){
            return $Monthly_PT;
        }else if($payfrequency == 'Weekly' && $grosspay >= $Weekly_PT){
            return $Weekly_PT;
        }else if($payfrequency == 'Fortnightly' && $grosspay >= $Fortnightly_PT){
            return $Fortnightly_PT;
        }else if($payfrequency == 'Four Weekly' && $grosspay >= $FourWeekly_PT){
            return $FourWeekly_PT;
        }else{
            return 0;
        }
    }

    //Calculate Upper Earnings Limit (UEL)
    public function calculateUEL($grosspay,$payfrequency){
        $annual_UEL = 50270;
        $Monthly_UEL = $annual_UEL/12;
        $Weekly_UEL = $annual_UEL/52;
        $Fortnightly_UEL = $annual_UEL/26;
        $FourWeekly_UEL = $annual_UEL/13;

        if($payfrequency == 'Monthly' && $grosspay >= $Monthly_UEL ){
            return $Monthly_UEL;
        }else if($payfrequency == 'Weekly' && $grosspay >= $Weekly_UEL){
            return $Weekly_UEL;
        }else if($payfrequency == 'Fortnightly' && $grosspay >= $Fortnightly_UEL){
            return $Fortnightly_UEL;
        }else if($payfrequency == 'Four Weekly' && $grosspay >= $FourWeekly_UEL){
            return $FourWeekly_UEL;
        }else{
            return 0;
        }
    }

    public function calculateApprenticeshipLevy($totalPayBill)
    {
        $threshold = 3000000; // £3,000,000 threshold
        $rate = 0.005; // 0.5%
        
        // Calculate levy only if total pay bill exceeds £3M
        return max(0, ($totalPayBill - $threshold) * $rate);
    }

    
    public function calculateNicCompensationOnStatutory($smp_paid)
    {
        $company=Company::first();
        $ser_eligible= $company->small_employer_relief_eligible; //Small Employers’ Relief 
        return $ser_eligible ? ($smp_paid * 1.03) : ($smp_paid * 0.92);
    }
}
