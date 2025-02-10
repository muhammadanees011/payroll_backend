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
        Schema::create('f_p_s_submissions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('payroll_id')->unsigned()->nullable();
            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('cascade');
            $table->string('tax_period');
            $table->date('pay_run_start_date');
            $table->date('pay_run_end_date');
            $table->date('submission_date')->nullable();
            $table->string('submission_xml')->nullable();
            $table->string('response_xml')->nullable();
            $table->enum('status',['Pending','Failed','Accepted','Rejected']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('f_p_s_submissions');
    }
};
