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
            $table->string('qualification');
            $table->string('course');
            $table->string('school');
            $table->string('start');
            $table->string('end');
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
