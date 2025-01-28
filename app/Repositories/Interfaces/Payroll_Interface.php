<?php

namespace App\Repositories\Interfaces;

interface Payroll_Interface {

    public function set_loan_repayments($employee_id,$pay_frequancy, $employeePayroll); 
    public function update_payroll_calculations($employee_id); 
}