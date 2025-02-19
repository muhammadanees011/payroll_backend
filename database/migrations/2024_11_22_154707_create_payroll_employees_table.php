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
        Schema::create('payroll_employees', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->bigInteger('payroll_id')->unsigned()->nullable();
            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('cascade');
            $table->bigInteger('pay_schedule_id')->unsigned()->nullable();
            $table->foreign('pay_schedule_id')->references('id')->on('pay_schedules')->onDelete('cascade');
            $table->double('gross_pay')->nullable();
            $table->double('net_pay')->nullable();
            $table->double('hours_worked')->nullable();
            $table->double('hourly_rate')->nullable();
            $table->double('base_pay')->nullable();
            $table->double('paye_income_tax')->nullable();
            $table->double('employee_nic')->nullable();
            $table->double('employee_pension')->nullable();
            $table->double('employer_nic')->nullable();
            $table->double('employer_pension')->nullable();
            $table->double('student_loan')->nullable();
            $table->double('pg_loan')->nullable();
            $table->double('sick_pay')->nullable();
            $table->double('paternity_pay')->nullable();
            $table->double('maternity_pay')->nullable();
            $table->double('adoption_pay')->nullable();
            $table->double('shared_parental_pay')->nullable();
            $table->double('parental_bereavement_pay')->nullable();

            $table->enum('salary_type',['Salaried','Hourly']);
            $table->enum('status',['active','archived','history'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_employees');
    }
};
