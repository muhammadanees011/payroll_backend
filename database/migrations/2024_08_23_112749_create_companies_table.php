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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_structure');
            $table->string('post_code');
            $table->string('address_line_1');
            $table->string('address_line_2');
            $table->string('city');
            $table->string('country');
            $table->string('registration_number')->nullable();
            $table->string('holiday_year_start_month');
            $table->string('director_name');
            $table->boolean('authorized_to_act');
            $table->boolean('agreed_to_terms');
            $table->boolean('planning_to_pay_myself');
            $table->boolean('planning_to_pay_employees');
            $table->boolean('no_payment_for_3_months');
            $table->date('first_payday');
            $table->boolean('is_first_payday_of_year');
            $table->boolean('is_first_payroll_of_company')->nullable();
            $table->string('payroll_provider')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
