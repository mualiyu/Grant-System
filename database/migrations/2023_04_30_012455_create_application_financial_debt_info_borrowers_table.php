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
        Schema::create('application_financial_debt_info_borrowers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_financial_debt_id');
            $table->longText('name')->nullable();
            $table->string('rc_number')->nullable();
            $table->longText('address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_financial_debt_info_borrowers');
    }
};
