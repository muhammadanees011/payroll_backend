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
        Schema::create('p32_taxes_filings', function (Blueprint $table) {
            $table->id();
            $table->integer('tax_month');
            $table->string('tax_year');
            $table->double('total_paye')->default(0.00);
            $table->double('gross_national_insurance')->default(0.00);
            $table->double('claimed_employment_allowance')->default(0.00);
            $table->double('total_statutory_recoveries')->default(0.00);
            $table->double('apprentice_levy')->default(0.00);
            $table->double('cis_deductions')->default(0.00);
            $table->double('amount_due')->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p32_taxes_filings');
    }
};
