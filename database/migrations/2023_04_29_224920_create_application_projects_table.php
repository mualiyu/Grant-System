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
            $table->string('name');
            $table->string('address');
            $table->string('date_of_contract')->nullable();
            $table->string('employer');
            $table->string('location');
            $table->longText('description');
            $table->string('date_of_completion')->nullable();
            $table->string('project_cost');
            $table->string('role_of_applicant');
            $table->string('equity')->nullable();
            $table->string('implemented')->nullable();
            $table->string('subcontactor_role')->nullable();
            $table->string('award_letter')->nullable();
            $table->string('interim_valuation_cert')->nullable();
            $table->string('certificate_of_completion')->nullable();
            $table->string('evidence_of_equity')->nullable();
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
