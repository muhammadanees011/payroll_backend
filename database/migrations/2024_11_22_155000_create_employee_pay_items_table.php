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
        Schema::create('employee_pay_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->bigInteger('payroll_id')->unsigned()->nullable();
            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('cascade');
            $table->bigInteger('pay_item_id')->unsigned()->nullable();
            $table->foreign('pay_item_id')->references('id')->on('pay_items')->onDelete('cascade');
            $table->bigInteger('salary_type_id')->unsigned()->nullable();
            $table->foreign('salary_type_id')->references('id')->on('salary_types')->onDelete('cascade');
            $table->double('units')->nullable();
            $table->double('hours')->nullable();
            $table->double('salary_rate')->nullable();
            $table->double('amount')->nullable(); //for all payitem,hourly and salary
            $table->enum('type',['Salary','PayItem']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_pay_items');
    }
};
