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
        Schema::create('employee_paternity_leaves', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->date('expected_due_date')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('second__block_start_date')->nullable();
            $table->date('second_block_end_date')->nullable();
            $table->double('average_weekly_earnings')->nullable();
            $table->enum('leave_type',['My employee will take 1 week of leave','My employee will take 2 weeks of leave in a row','My employee will take 2 weeks of leave in seperate blocks']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_paternity_leaves');
    }
};
