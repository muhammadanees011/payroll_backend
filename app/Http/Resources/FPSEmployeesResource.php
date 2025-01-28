<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FPSEmployeesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'national_insurance_number' => $this->employee->nino,
            'name' => [
                'title'    => $this->employee->title,
                'forename' => $this->employee->forename,
                'surname'  => $this->employee->surname,
            ],

            'address' => [
                'lines' => [
                    $this->employee->address_line1,
                    $this->employee->address_line2,
                    $this->employee->city,
                    ],
                'postcode' => $this->employee->postcode,
            ],

            'birth_date' => $this->employee->dob, // Date of birth
            'gender'     => $this->employee->gender == 'Male' ? 'M':($this->employee->gender == 'Female' ? 'F':null), // gender
            'pay_id'     => $this->employee->payroll_id, 

            'payment_frequency' =>  $this->payschedule->pay_frequency =='Monthly' ? 'M1':($this->payschedule->pay_frequency =='Weekly' ? 'W1':($this->payschedule->pay_frequency =='Fortnightly' ? 'W2':($this->payschedule->pay_frequency =='Four Weekly' ? 'W4':('')))),         // Pay frequency (e.g. W1 = Weekly, W2 = Fortnightly, W4 = 4 Weekly, M1 = Calendar Monthly, etc)
            'payment_date'      =>  $this->payroll->pay_date, // Payment date
            'payment_month'     => '7',          // Monthly period number
            'payment_periods'   => '1',          // Number of earnings periods covered by payment
            'payment_hours'     => '35',       // Number of normal hours worked (approximately)
            'payment_tax_code'  => $this->employeestarterdetail->tax_code,       // Tax code and basis
            'payment_taxable'   => number_format((float)$this->gross_pay, 2, '.', ''),    // Taxable pay in this pay period including payrolled benefits in kind
            'payment_tax'       => number_format((float)$this->paye_income_tax, 2, '.', ''),     // Value of tax deducted or refunded from this payment

            'ni_letter' => $this->employee->ni_category, // National Insurance Category letter in pay period
            'to_date_taxable' => $this->employeeytd ? number_format((float)$this->employeeytd->gross_for_tax, 2, '.', '') :'0.00', // Taxable pay to date in this employment including taxable benefits undertaken through payroll
            'to_date_tax'     => $this->employeeytd ? number_format((float)$this->employeeytd->tax_deducted, 2, '.', '') :'0.00',  // Total tax to date in this employment including this submission

            'ni_gross_nics_pd'  => number_format((float)$this->gross_pay, 2, '.', ''), // Gross earnings for NICs in this period.
            'ni_gross_nics_ytd' => $this->employeeytd ? number_format((float)$this->employeeytd->gross_pay_for_national_insurance, 2, '.', '') :'0.00', // Gross earnings for NICs year to date.

            'ni_total_lel_ytd' => $this->employeeytd ? number_format((float)$this->employeeytd->earnings_at_LEL, 2, '.', '') :'0.00',  // Value of Earnings at Lower Earnings Limit Year to Date.
            'ni_total_pt_ytd'  => $this->employeeytd ? number_format((float)$this->employeeytd->earnings_at_PT, 2, '.', '') :'0.00', // Value of Earnings above Lower Earnings Limit to Primary Threshold Year to Date.
            'ni_total_uel_ytd' => $this->employeeytd ? number_format((float)$this->employeeytd->earnings_to_UEL, 2, '.', '') :'0.00',    // Value of Earnings from Upper Accrual Point up to Upper Earnings Limit Year to Date.

            'ni_total_nic_pd'  => number_format((float)$this->employer_nic, 2, '.', ''),  // Total of employer NI Contributions in this period.
            'ni_total_nic_ytd' => $this->employeeytd ? number_format((float)$this->employeeytd->employer_national_insurance, 2, '.', '') :'0.00',  // Total of employer NI contributions year to date.

            'ni_total_contribution_pd'  => number_format((float)$this->employee_nic, 2, '.', ''), // Employees contributions due on all earnings in this pay period.
            'ni_total_contribution_ytd' => $this->employeeytd ? number_format((float)$this->employeeytd->employee_national_insurance, 2, '.', '') :'0.00', // Employees contributions due on all earnings year to date.
        ];
    }
}
