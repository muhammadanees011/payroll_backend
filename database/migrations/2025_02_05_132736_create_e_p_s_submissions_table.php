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
        Schema::create('e_p_s_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('tax_month');
            $table->string('tax_year');
            $table->date('submission_date')->nullable();
            $table->string('submission_xml')->nullable();
            $table->string('response_xml')->nullable();
            $table->enum('type',['Bank Detail Submission','No Payment Submission']);
            $table->enum('status',['Pending','Failed','Accepted','Rejected']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('e_p_s_submissions');
    }
};
