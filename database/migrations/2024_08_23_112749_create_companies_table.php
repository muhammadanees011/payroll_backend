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
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('country')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('holiday_year_start_month')->nullable();
            $table->string('director_name');
            $table->boolean('authorized_to_act');
            $table->boolean('agreed_to_terms');
            $table->json('company_payee')->nullable();
            $table->date('first_payday')->nullable();
            $table->boolean('is_first_payday_of_year')->nullable();
            $table->boolean('is_first_payroll_of_company')->nullable();
            $table->string('payroll_provider')->nullable();
            $table->integer('step')->default(0);
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
