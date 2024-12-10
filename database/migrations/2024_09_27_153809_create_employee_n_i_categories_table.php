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
        Schema::create('employee_n_i_categories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('ni_category')->nullable();
            $table->double('gross_earnings_for_nic_ytd')->nullable();
            $table->double('earnings_at_lel_ytd')->nullable();
            $table->string('earnings_at_pt_ytd')->nullable();
            $table->string('earnings_at_uel_ytd')->nullable();
            $table->string('employee_nic_ytd')->nullable();
            $table->string('employer_nic_ytd')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_n_i_categories');
    }
};
