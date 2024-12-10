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
        Schema::create('employee_year_to_dates', function (Blueprint $table) {
            $table->id();
            
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            //TAX
            $table->double('gross_for_tax')->default(0.00)->nullable();
            $table->double('tax_deducted')->default(0.00)->nullable();     
            $table->double('student_loan')->default(0.00)->nullable();
            $table->double('postgraduate_loan')->default(0.00)->nullable();
            $table->double('employee_pension')->default(0.00)->nullable();
            $table->double('employer_pension')->default(0.00)->nullable();

            //BENEFITS
            $table->double('benefit_in_kind_payrolled_amount')->default(0.00)->nullable();
            $table->double('employe_net_pay_pension')->default(0.00)->nullable();

            //Statutory Payments
            $table->double('statutory_maternity_pay')->default(0.00)->nullable();
            $table->double('statutory_paternity_pay')->default(0.00)->nullable();
            $table->double('statutory_adoption_pay')->default(0.00)->nullable();
            $table->double('statutory_sick_pay')->default(0.00)->nullable();
            $table->double('parental_bereavement')->default(0.00)->nullable();
            $table->double('shared_parental_pay')->default(0.00)->nullable();

            //National Insurance
            $table->string('national_insurance_category')->nullable();
            $table->double('earnings_at_LEL')->default(0.00)->nullable();
            $table->double('earnings_at_PT')->default(0.00)->nullable();
            $table->double('earnings_to_UEL')->default(0.00)->nullable();
            $table->double('employee_national_insurance')->default(0.00)->nullable();
            $table->double('employer_national_insurance')->default(0.00)->nullable();
            $table->double('gross_pay_for_national_insurance')->default(0.00)->nullable();

            //National Insurance Director
            $table->double('director_earnings_at_LEL')->default(0.00)->nullable();
            $table->double('director_earnings_to_PT')->default(0.00)->nullable();
            $table->double('director_earnings_to_UEL')->default(0.00)->nullable();
            $table->double('director_national_insurance')->default(0.00)->nullable();
            $table->double('director_employer_national_insurance')->default(0.00)->nullable();
            $table->double('director_gross_pay_for_national_insurance')->default(0.00)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_year_to_dates');
    }
};
