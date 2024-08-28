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
        Schema::create('statutorypays', function (Blueprint $table) {
            $table->id();
            $table->double('recovered_smp_ytd', 15, 2)->default(0.00);
            $table->double('smp_ni_compensation_ytd', 15, 2)->default(0.00);
            $table->double('recovered_spp_ytd', 15, 2)->default(0.00);
            $table->double('spp_ni_compensation_ytd', 15, 2)->default(0.00);
            $table->double('recovered_sap_ytd', 15, 2)->default(0.00);
            $table->double('sap_ni_compensation_ytd', 15, 2)->default(0.00);
            $table->double('recovered_spbp_ytd', 15, 2)->default(0.00);
            $table->double('spbp_ni_compensation_ytd', 15, 2)->default(0.00); 
            $table->double('recovered_shpp_ytd', 15, 2)->default(0.00);
            $table->double('shpp_ni_compensation_ytd', 15, 2)->default(0.00); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statutorypays');
    }
};
