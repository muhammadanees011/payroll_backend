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
        Schema::create('hmrc_settings', function (Blueprint $table) {
            $table->id();
            $table->string('account_office_reference');
            $table->string('paye_reference');
            $table->string('taxpayer_reference');
            $table->boolean('eligible_for_employment_allowance');
            $table->boolean('eligible_for_small_employers_relief');
            $table->string('business_sector');
            $table->string('hmrc_gateway_id');
            $table->string('hmrc_password');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hmrc_settings');
    }
};
