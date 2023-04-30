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
        Schema::create('application_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id')->unsigned();
            $table->unsignedBigInteger('application_id')->unsigned();
            $table->string('name');
            $table->string('registration_date');
            $table->string('cac_number');
            $table->string('address');
            $table->string('description')->nullable();
            $table->string('website')->nullable();
            $table->string('owner');
            $table->string('authorised_personel')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_profiles');
    }
};
