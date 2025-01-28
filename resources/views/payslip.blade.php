
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XEPayroll</title>

<style>
  /* Overall Styling */
  .payslip-container {
    font-family: Arial, sans-serif;
    color: #000;
    background-color: #fff;
    padding: 20px;
    width: 100%;
    max-width: 800px;
    margin: auto;
    line-height: 1.3; /* Reduced line height for tighter spacing */
  }
  
  /* Header */
  .header h3 {
    font-size: 15px;
    font-weight: bold;
    text-align: start;
    margin-bottom: 10px;
  }
  
  hr {
    margin: 10px 0;
  }
  
  /* Two-column Layout */
  .details-container {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
  }
  
  .details-container > div {
    width: 48%;
  }
  
.summary{
    width: 100%;
    margin-top: 25px;
  }
  
  /* Summary Rows */
.summary .row {
    width: 100%;
    display: flex;                  /* Makes the row only as wide as the content */
    flex-direction:column;
    justify-content: space-between !important;
    margin-bottom: 1px; /* Reduced space between rows */
  }

  .summary .row p {
    width:50% !important;
    background-color:red;
  }


  .summary .label {
    font-weight: bold;
  }
  
  /* Table Section */
  .table-section table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-size: 12px;
  }
  
  .table-section th,
  .table-section td {
    text-align: left;
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 5px;
    padding-right: 5px;
  }

  .table-section td{
    border-bottom: 1px solid #ccc;
  }

  .table-section tr{
    background-color: #F4F1F1;
  }
  
  .table-section th {
    background-color: #4CAF50; /* Dark Green header */
    color: white;
    font-weight: bold;
  }
  
/* Totals Section */
.totals-container {
    display: flex;
    justify-content: space-between;
    margin-top: 5px;
    width: 100%;
}

.totals-container > .table-section {
    width: 49%; /* Each child will occupy 48% of the width */
}

.totals-container {
    gap: 2%; /* This will create a 4% gap between the two children */
}
  
  .totals {
    width: 49%;
  }
  
  .totals h4 {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 10px;
  }
  
  .totals .label {
    font-weight: bold;
    font-size: 12px;
  }

  p{
    line-height: 7px;
    font-size: 13px;
  }
  </style>

</head>

<body>

<div id="payslip" class="payslip-container">
  <div class="header">
    <h3>Top-Payroll</h3>
    <hr />
  </div>

  <!-- Employee Details -->
  <div class="summary">
    <div class="row">
      <span class="employee-name" style="display: inline-block; width: 45%;"><strong>{{ $employeePaySlip['name'] }}</strong></span>
      <span style="display: inline-block; width: 45%; text-align: right;"><strong>{{ $employeePaySlip['company'] ? $employeePaySlip['company']['name'] : '' }}</strong></span>
    </div>
    <div class="row">
      <span style="display: inline-block; width: 45%;"><strong>{{ $employeePaySlip['employee_address_line1'] }}</strong></span>
      <span style="display: inline-block; width: 45%; text-align: right;"><strong>{{ $employeePaySlip['company'] ? $employeePaySlip['company']['address_line_1'] : '' }}</strong></span>
    </div>
    <div class="row">
      <span style="display: inline-block; width: 45%;"><strong>{{ $employeePaySlip['employee_address_line2'] }}, {{ $employeePaySlip['employee_city'] }}</strong></span>
      <span style="display: inline-block; width: 45%; text-align: right;"><strong>{{ $employeePaySlip['company'] ? $employeePaySlip['company']['address_line_2'] : '' }}</strong></span>
    </div>
    <div class="row">
      <span style="display: inline-block; width: 45%;"></span>
      <span style="display: inline-block; width: 45%; text-align: right;"><strong>{{ $employeePaySlip['company'] ? $employeePaySlip['company']['post_code'] : '' }}, {{ $employeePaySlip['company'] ? $employeePaySlip['company']['city'] : '' }}</strong></span>
    </div>
  </div>

  <!-- Summary Section -->
  <div class="summary">
    <div class="row">
      <span style="display: inline-block; width: 45%;"><span>Payroll ID:</span> <strong>{{ $employeePaySlip['employee_payrollId'] }}</strong></span>
      <span style="display: inline-block; width: 45%; text-align: right;"><span>Tax Code:</span> <strong>{{ $employeePaySlip['tax_code'] }}</strong></span>
    </div>
    <div class="row">
      <span style="display: inline-block; width: 45%;"><span>NI Number:</span> <strong>{{ $employeePaySlip['employee_nino'] }}</strong></span>
      <span style="display: inline-block; width: 45%; text-align: right;"><span>NI Category:</span> <strong>{{ $employeePaySlip['ni_category'] }}</strong></span>
    </div>
    <div class="row">
      <span style="display: inline-block; width: 45%;"><span class="label">Tax Period:</span> <strong>{{ $employeePaySlip['tax_period'] }}</strong></span>
      <span style="display: inline-block; width: 45%; text-align: right;"><span class="label">Net Pay:</span> {{ formatCurrency($employeePaySlip['net_pay']) }}</span>
    </div>
    <div class="row">
      <span style="display: inline-block; width: 45%;">{{ \Carbon\Carbon::parse($employeePaySlip['pay_run_start_date'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($employeePaySlip['pay_run_end_date'])->format('d/m/Y') }}</span>
      <span style="display: inline-block; width: 45%; text-align: right;">{{ \Carbon\Carbon::parse($employeePaySlip['pay_date'])->format('d/m/Y') }}</span>
    </div>
  </div>

  <!-- Table Section -->
  <div class="table-section">
    <table>
      <thead>
        <tr>
          <th>Description</th>
          <th>Basis</th>
          <th>Rate</th>
          <th>Addition/Deduction</th>
          <th>(SUB)Total</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Base Pay</td>
          <td>{{ formatCurrency($employeePaySlip['base_pay']) }}</td>
          <td></td>
          <td></td>
          <td>{{ formatCurrency($employeePaySlip['base_pay']) }}</td>
        </tr>
        @if($employeePaySlip['pg_loan'])
        <tr>
          <td>PG Student Loan</td>
          <td></td>
          <td></td>
          <td>- {{ formatCurrency($employeePaySlip['pg_loan']) }}</td>
          <td>{{ formatCurrency($employeePaySlip['base_pay'] - $employeePaySlip['pg_loan']) }}</td>
        </tr>
        @endif
        @if($employeePaySlip['student_loan'])
        <tr>
          <td>Student Loan</td>
          <td></td>
          <td></td>
          <td>- {{ formatCurrency($employeePaySlip['student_loan']) }}</td>
          <td>{{ formatCurrency($employeePaySlip['base_pay'] - ($employeePaySlip['pg_loan'] + $employeePaySlip['student_loan'])) }}</td>
        </tr>
        @endif
        <tr>
          <td>Employee NIC</td>
          <td></td>
          <td>{{ $employeePaySlip['ni_category'] }}</td>
          <td>- {{ formatCurrency($employeePaySlip['employee_nic']) }}</td>
          <td>{{ formatCurrency($employeePaySlip['base_pay'] - ($employeePaySlip['pg_loan'] + $employeePaySlip['student_loan'] + $employeePaySlip['employee_nic'])) }}</td>
        </tr>
        <tr>
          <td>PAYE Income Tax</td>
          <td></td>
          <td>{{ $employeePaySlip['tax_code'] }}</td>
          <td>- {{ formatCurrency($employeePaySlip['paye_income_tax']) }}</td>
          <td>{{ formatCurrency($employeePaySlip['base_pay'] - ($employeePaySlip['pg_loan'] + $employeePaySlip['student_loan'] + $employeePaySlip['paye_income_tax'] + $employeePaySlip['employee_nic'])) }}</td>
        </tr>
        <tr>
          <td>Employee Pension</td>
          <td></td>
          <td></td>
          <td>- {{ formatCurrency($employeePaySlip['employee_pension']) }}</td>
          <td>{{ formatCurrency($employeePaySlip['base_pay'] - ($employeePaySlip['pg_loan'] + $employeePaySlip['student_loan'] + $employeePaySlip['employee_pension'] + $employeePaySlip['paye_income_tax'] + $employeePaySlip['employee_nic'])) }}</td>
        </tr>
        @if($employeePaySlip['payitems'])
        @foreach ($employeePaySlip['payitems'] as $index => $item)
          <tr>
            <td>{{ $item['payitem']['name'] }}</td>
            <td></td>
            <td></td>
            <td>{{ formatCurrency($item['amount']) }}</td>
            <td>{{ calculateSubTotal($employeePaySlip,$index) }}</td>
          </tr>
        @endforeach
        @endif
        @if($employeePaySlip['sick_pay'])
        <tr>
          <td>Statutory Sick Pay</td>
          <td></td>
          <td></td>
          <td> {{ formatCurrency($employeePaySlip['sick_pay']) }}</td>
          <td>{{ formatCurrency(($employeePaySlip['base_pay'] + $employeePaySlip['sick_pay'])  - ($employeePaySlip['pg_loan'] + $employeePaySlip['student_loan'] + $employeePaySlip['employee_pension'] + $employeePaySlip['paye_income_tax'] + $employeePaySlip['employee_nic'])) }}</td>
        </tr>
        @endif
        @if($employeePaySlip['paternity_pay'])
        <tr>
          <td>Paternity Pay</td>
          <td></td>
          <td></td>
          <td> {{ formatCurrency($employeePaySlip['paternity_pay']) }}</td>
          <td>{{ formatCurrency(($employeePaySlip['base_pay'] + $employeePaySlip['sick_pay'] + $employeePaySlip['paternity_pay'])  - ($employeePaySlip['pg_loan'] + $employeePaySlip['student_loan'] + $employeePaySlip['employee_pension'] + $employeePaySlip['paye_income_tax'] + $employeePaySlip['employee_nic'])) }}</td>
        </tr>
        @endif
        <tr>
          <td>Net Pay</td>
          <td></td>
          <td></td>
          <td></td>
          <td>{{ formatCurrency($employeePaySlip['net_pay']) }}</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Totals Section -->
  <div class="totals-container" style="margin-top:40px;">

    <!-- Employee Totals -->
    <div style="display: inline-block; width: 47%;" class="table-section">
      <table>
        <thead>
          <tr>
            <th>Employees Total</th>
            <th>This Period</th>
            <th>Years Total</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Gross Earnings</td>
            <td>{{ formatCurrency($employeePaySlip['gross_pay']) }}</td>
            <td>{{ formatCurrency($employeePaySlip['yearlyEarnings'] ? $employeePaySlip['yearlyEarnings']['gross_earnings_this_year'] : 0) }}</td>
          </tr>
          <tr>
            <td>Taxable Earnings</td>
            <td>£1,000.00</td>
            <td>£1,000.00</td>
          </tr>
          <tr>
            <td>Employee NIC</td>
            <td>{{ formatCurrency($employeePaySlip['employee_nic']) }}</td>
            <td>{{ formatCurrency($employeePaySlip['yearlyEarnings'] ? $employeePaySlip['yearlyEarnings']['employee_nic_this_year'] : 0) }}</td>
          </tr>
          <tr>
            <td>PAYE Income Tax</td>
            <td>{{ formatCurrency($employeePaySlip['paye_income_tax']) }}</td>
            <td>{{ formatCurrency($employeePaySlip['yearlyEarnings'] ? $employeePaySlip['yearlyEarnings']['paye_income_tax_this_year'] : 0) }}</td>
          </tr>
          <tr>
            <td>Employee Pension</td>
            <td>{{ formatCurrency($employeePaySlip['employee_pension']) }}</td>
            <td>{{ formatCurrency($employeePaySlip['yearlyEarnings'] ? $employeePaySlip['yearlyEarnings']['employee_pension_this_year'] : 0) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div style="display: inline-block; width: 4%;" class="table-section">
    </div>
    <!-- Employer Totals -->
    <div style="display: inline-block; width: 47%; text-align: right; margin-bottom:85px;" class="table-section">
      <table>
        <thead>
          <tr>
            <th>Employers Total</th>
            <th>This Period</th>
            <th>Years Total</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Employer NIC</td>
            <td>{{ formatCurrency($employeePaySlip['employer_nic']) }}</td>
            <td>{{ formatCurrency($employeePaySlip['yearlyEarnings'] ? $employeePaySlip['yearlyEarnings']['employer_nic_this_year'] : 0) }}</td>
          </tr>
          <tr>
            <td>Employer Pension</td>
            <td>{{ formatCurrency($employeePaySlip['employer_pension']) }}</td>
            <td>{{ formatCurrency($employeePaySlip['yearlyEarnings'] ? $employeePaySlip['yearlyEarnings']['employer_pension_this_year'] : 0) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</div>


</body>

</html>

