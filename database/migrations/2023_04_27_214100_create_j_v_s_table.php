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
        Schema::create('j_v_s', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('type')->nullable();
            $table->string('address')->nullable();
            $table->string('rc_number')->nullable();
            $table->longText('document')->nullable();

            $table->string('evidence_of_cac')->nullable();
            $table->string('company_income_tax')->nullable();
            $table->string('audited_account')->nullable();
            $table->string('letter_of_authorization')->nullable();
            $table->string('sworn_affidavits')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('j_v_s');
    }
};
