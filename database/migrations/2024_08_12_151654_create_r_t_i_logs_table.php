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
        Schema::create('r_t_i_logs', function (Blueprint $table) {
            $table->id();
            $table->longText('request_url')->nullable();
            $table->longText('request_xml')->nullable();
            $table->longText('request_correlation')->nullable();
            $table->longText('request_irmark')->nullable();
            $table->longText('response_xml')->nullable();
            $table->longText('response_qualifier')->nullable();
            $table->longText('response_function')->nullable();
            $table->longText('response_correlation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('r_t_i_logs');
    }
};
