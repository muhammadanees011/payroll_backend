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
            $table->bigInteger('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->string('name');
            $table->enum('pay_frequency',['Weekly','Fortnightly','Four Weekly','Monthly']);
            $table->string('paydays');
            $table->date('first_paydate');
            $table->enum('day_rate_method',['Calander Month','Yearly Working Days']);
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
