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
            $table->boolean('taxable')->nullable();
            $table->boolean('pensionable')->nullable();
            $table->boolean('subject_to_national_insurance')->nullable();
            $table->enum('payment_type',['Gross Addition','Gross Deduction','Net Addition','Net Deduction']);
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
