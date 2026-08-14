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
        Schema::create('public_files', function (Blueprint $table) {
            $table->id();
            $table->string('applicant_name');
            $table->string('email');
            $table->string('contact_number');
            $table->string('subject');
            $table->text('remarks')->nullable();
            $table->string('attachment_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_files');
    }
};
