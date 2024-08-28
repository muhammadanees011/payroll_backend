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
        Schema::create('pay_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->boolean('is_benefit_in_kind');
            $table->boolean('taxable');
            $table->boolean('pensionable');
            $table->boolean('subject_to_national_insurance');
            $table->enum('payment_type',['gross_addition','gross_deduction','net_addition','net_deduction']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_items');
    }
};
