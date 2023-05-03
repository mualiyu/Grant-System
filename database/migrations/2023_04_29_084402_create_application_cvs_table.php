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
        Schema::create('application_cvs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->string('name');
            // $table->string('dob');
            $table->string('language')->nullable();
            $table->string('membership')->nullable();
            // $table->string('countries_experience');
            // $table->longText('work_undertaken')->nullable();
            $table->string('coren_license_number')->nullable();
            $table->string('coren_license_document')->nullable();
            $table->string('education_certificate')->nullable();
            $table->string('professional_certificate')->nullable();
            $table->string('cv')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_cvs');
    }
};
