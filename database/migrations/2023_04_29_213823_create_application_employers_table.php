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
        Schema::create('application_employers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_cv_id');
            $table->string('name')->nullable();
            $table->string('position')->nullable();
            $table->string('start')->nullable();
            $table->string('end')->nullable();
            $table->longText('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_employers');
    }
};
