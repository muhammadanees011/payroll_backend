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
        Schema::create('allowances', function (Blueprint $table) {
            $table->id();
            $table->double('allowance_claimed')->nullable();
            $table->double('allowance_remaining')->nullable();
            $table->double('pay_bill_ytd')->nullable();
            $table->double('levy_due_ytd')->nullable();
            $table->boolean('shares_apprentice_levy_allowance')->nullable();
            $table->enum('type',['employment_allowance','apprenticeship_levy']);
            $table->enum('status',['enabled','disabled']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allowances');
    }
};
