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
            $table->bigInteger('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->string('account_office_reference');
            $table->string('paye_reference');
            $table->string('taxpayer_reference');
            $table->json('employment_allowance');
            $table->json('business_sector');
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
