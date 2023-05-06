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
        Schema::create('application_financial_debt_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->longText('project_name')->nullable();
            $table->longText('location')->nullable();
            $table->string('sector')->nullable();
            $table->string('aggregate_amount')->nullable();
            $table->string('date_of_financial_close')->nullable();
            // $table->string('date_of_first_drawdown')->nullable();
            // $table->string('date_of_final_drawdown')->nullable();
            // $table->string('tenor_of_financing')->nullable();
            $table->string('evidence_of_support')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_financial_debt_infos');
    }
};
