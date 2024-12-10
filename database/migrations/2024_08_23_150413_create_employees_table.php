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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->enum('title',['mr','miss','mrs','ms']);
            $table->string('forename');
            $table->string('surname');
            $table->enum('gender',['male','female']);
            $table->date('dob');
            $table->string('work_email');
            $table->string('telephone');
            $table->string('ni_category');
            $table->string('nino');
            $table->string('payroll_id')->unique()->nullable();
            $table->date('employement_start_date')->nullable();
            $table->string('postcode');
            $table->string('address_line1');
            $table->string('address_line2');
            $table->string('city');
            $table->string('country');
            $table->enum('status',['Pending Information','Active']);
            $table->integer('step')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
