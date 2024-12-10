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
        Schema::create('employee_starter_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('tax_code')->nullable();
            $table->double('previous_taxable_salary')->nullable();
            $table->double('previous_tax_paid')->nullable();
            $table->enum('tax_basis',['Cumulative','Non-Cumulative'])->nullable();
            $table->string('starter_declaration')->nullable();
            $table->string('current_employment_taxable_pay_ytd')->nullable();
            $table->string('current_employment_tax_paid_ytd')->nullable();
            $table->string('employee_pension_contributions_ytd')->nullable();
            $table->string('payrolled_benefits_ytd')->nullable();
            $table->enum('starter_type',['New Employee With P45','New Employee Without P45','Existing Employee'])->nullable();
            $table->enum('employment_statutory_payments_loans',['Yes','No'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_starter_details');
    }
};
