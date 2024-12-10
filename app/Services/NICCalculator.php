<?php

namespace App\Services;

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
}
