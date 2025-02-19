<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pay_schedule_id')->unsigned()->nullable();
            $table->foreign('pay_schedule_id')->references('id')->on('pay_schedules')->onDelete('cascade');

            $table->string('tax_period');
            $table->date('pay_run_start_date');
            $table->date('pay_run_end_date');
            $table->date('pay_date');

            $table->double('net_pay')->nullable();
            $table->double('pension')->nullable();
            $table->double('paye_and_nic')->nullable();

            $table->double('base_pay_additions')->nullable();
            $table->double('gross_additions')->nullable();
            $table->double('net_additions')->nullable();

            $table->double('gross_deductions')->nullable();
            $table->double('income_tax_deductions')->nullable();
            $table->double('student_loans_deductions')->nullable();
            $table->double('postgraduate_loans_deductions')->nullable();
            $table->double('employees_nic_deductions')->nullable();
            $table->double('employees_pension_deductions')->nullable();
            $table->double('net_deductions')->nullable();
            $table->double('employer_nic_deductions')->nullable();
            $table->double('employer_pension_deductions')->nullable();

            $table->double('total_payroll_cost')->nullable();
            $table->integer('total_employees')->nullable();

            $table->double('apprentice_levy')->nullable();
            $table->double('cis_deduction')->nullable();

            $table->double('statutory_maternity_pay')->nullable();
            $table->double('statutory_paternity_pay')->nullable();
            $table->double('statutory_adoption_pay')->nullable();
            $table->double('statutory_shared_parental_pay')->nullable();

            $table->enum('status',['active','history','draft','archived']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
