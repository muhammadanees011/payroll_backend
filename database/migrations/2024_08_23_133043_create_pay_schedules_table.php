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
        Schema::create('pay_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('pay_frequency',['Weekly','Fortnightly','Four Weekly','Monthly']);
            $table->string('paydays');
            $table->date('first_paydate');
            $table->enum('day_rate_method',['calander_month','yearly_working_days']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_schedules');
    }
};
