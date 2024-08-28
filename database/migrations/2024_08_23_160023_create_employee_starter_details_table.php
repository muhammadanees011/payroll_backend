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
        Schema::create('employee_starter_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('tax_code')->nullable();
            $table->double('previous_taxable_salary')->nullable();
            $table->double('previous_tax_paid')->nullable();
            $table->enum('tax_basis',['cumulative','non_cumulative'])->nullable();
            $table->enum('starter_type',['new_employee_with_p45','new_employee_without_p45','existing_employee'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_starter_details');
    }
};
