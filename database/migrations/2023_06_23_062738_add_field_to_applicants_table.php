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
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('has_operated')->nullable();
            $table->string('has_designed')->nullable();
            $table->longText('cac_certificate')->nullable();
            $table->longText('tax_clearance_certificate')->nullable();
            $table->string('isApproved')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
