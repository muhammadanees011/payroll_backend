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
        Schema::create('loan_payment_plans', function (Blueprint $table) {
            $table->id();
            $table->string('payment_plan')->nullable();
            $table->string('plan_description')->nullable();
            $table->double('annual_threshold')->nullable();
            $table->double('monthly_threshold')->nullable();
            $table->double('weekly_threshold')->nullable();
            $table->double('fortnightly_threshold')->nullable();
            $table->double('fourweekly_threshold')->nullable();
            $table->integer('repay_percentage')->nullable();
            $table->enum('type',['student_loan','pg_loan']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_payment_plans');
    }
};
