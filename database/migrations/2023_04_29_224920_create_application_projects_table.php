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
        Schema::create('application_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->longText('name')->nullable();
            $table->longText('address')->nullable();
            $table->string('date_of_contract')->nullable();
            $table->longText('employer')->nullable();
            $table->string('location')->nullable();
            $table->longText('description')->nullable();
            $table->string('date_of_completion')->nullable();
            $table->string('project_cost')->nullable();
            $table->string('role_of_applicant')->nullable();
            $table->longText('geocoordinate')->nullable();
            // $table->string('implemented')->nullable();
            $table->string('subcontactor_role')->nullable();
            $table->string('award_letter')->nullable();
            $table->string('interim_valuation_cert')->nullable();
            $table->string('certificate_of_completion')->nullable();
            $table->string('evidence_of_completion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_projects');
    }
};
