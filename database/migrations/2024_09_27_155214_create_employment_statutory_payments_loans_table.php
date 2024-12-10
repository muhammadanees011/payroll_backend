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
        Schema::create('employment_statutory_payments_loans', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('total_statutory_sick_pay')->nullable();
            $table->double('total_statutory_maternity_pay')->nullable();
            $table->double('total_statutory_paternity_pay')->nullable();
            $table->string('total_shared_parental_pay')->nullable();
            $table->string('total_statutory_adoption_pay')->nullable();
            $table->string('total_statutory_parental_bereavement_pay')->nullable();
            $table->string('total_student_loan_deductions')->nullable();
            $table->string('total_postgraduate_loan_deductions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employment_statutory_payments_loans');
    }
};
