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
        Schema::create('employee_sick_leaves', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->double('average_weekly_earnings')->nullable();
            $table->integer('days_unavailable')->nullable();
            $table->boolean('statutory_eligibility')->nullable();
            $table->integer('statutory_waiting_days')->nullable();
            $table->integer('statutory_payable_days')->nullable();
            $table->enum('status',['paid','processed','pending','unpaid'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_sick_leaves');
    }
};
