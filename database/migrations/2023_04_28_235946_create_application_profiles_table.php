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
            $table->longText('name');
            $table->string('registration_date')->nullable();
            $table->longText('cac_number')->nullable();
            $table->longText('address')->nullable();
            $table->longText('description')->nullable();
            $table->longText('website')->nullable();
            $table->longText('owner')->nullable();
            $table->longText('authorised_personel')->nullable();
            $table->string('evidence_of_equipment_ownership')->nullable();
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
