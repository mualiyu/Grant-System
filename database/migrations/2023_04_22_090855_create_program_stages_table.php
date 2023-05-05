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
        Schema::create('program_stages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_id');
            // $table->unsignedBigInteger('region_id');
            $table->longText('name');
            $table->longText('description')->nullable();
            $table->string('start')->nullable();
            $table->string('end')->nullable();
            $table->string('document')->nullable();
            $table->string('isActive')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_stages');
    }
};
