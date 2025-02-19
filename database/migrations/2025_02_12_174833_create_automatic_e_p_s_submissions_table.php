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
        Schema::create('automatic_e_p_s_submissions', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('payroll_id')->unsigned()->nullable();
            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('cascade');
            $table->bigInteger('pay_schedule_id')->unsigned()->nullable();
            $table->foreign('pay_schedule_id')->references('id')->on('pay_schedules')->onDelete('cascade');

            $table->integer('tax_period');
            $table->string('pay_run_start_date');
            $table->string('pay_run_end_date');

            $table->double('statutory_maternity_pay')->nullable();
            $table->double('statutory_paternity_pay')->nullable();
            $table->double('statutory_sick_pay')->nullable();
            $table->double('statutory_adoption_pay')->nullable();
            $table->double('statutory_shared_parental_pay')->nullable();
            $table->double('statutory_parental_bereavement_pay')->nullable();

            $table->double('nic_compensation_on_smp')->nullable();
            $table->double('nic_compensation_on_spp')->nullable();
            $table->double('nic_compensation_on_sap')->nullable();
            $table->double('nic_compensation_on_shpp')->nullable();
            $table->double('nic_compensation_on_spbp')->nullable();

            $table->double('cis_deduction_suffered')->nullable();
            $table->enum('status',['Pending','Failed','Accepted','Rejected']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automatic_e_p_s_submissions');
    }
};
