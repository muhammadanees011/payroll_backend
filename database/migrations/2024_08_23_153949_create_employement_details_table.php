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
        Schema::create('employement_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->bigInteger('pay_schedule_id')->unsigned()->nullable();
            $table->foreign('pay_schedule_id')->references('id')->on('pay_schedules')->onDelete('cascade');
            $table->enum('salary_type',['Salaried','Hourly']);
            $table->double('anual_salary')->nullable();
            $table->double('monthly_salary')->nullable();
            $table->double('weekly_salary')->nullable();
            $table->double('expected_work_hours_per_week');
            $table->double('hourly_equivalent')->nullable();
            $table->enum('is_director_current_tax_year',['Yes','No'])->nullable();
            $table->date('date_appointed_director')->nullable();
            $table->date('date_ended_directorship')->nullable();
            $table->enum('calculation_method',['Standard Annual Method','Alternative Method'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employement_details');
    }
};
