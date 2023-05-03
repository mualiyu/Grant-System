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
        Schema::create('application_financial_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->string('total_assets')->nullable();
            $table->string('total_liability')->nullable();
            $table->string('total_networth')->nullable();
            $table->string('annual_turnover')->nullable();
            // $table->string('profit_before_taxes')->nullable();
            // $table->string('profit_after_taxes')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_financial_infos');
    }
};
