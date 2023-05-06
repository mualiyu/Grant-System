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
        Schema::create('application_education', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_cv_id');
            $table->string('qualification')->nullable();
            $table->string('course')->nullable();
            $table->string('school')->nullable();
            $table->string('start')->nullable();
            $table->string('end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_education');
    }
};
