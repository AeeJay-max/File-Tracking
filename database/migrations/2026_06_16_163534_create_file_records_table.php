<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_records', function (Blueprint $table) {
            $table->id();

            // 🔗 Relationships
            $table->foreignId('department_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // 📄 File Info
            $table->string('file_name');
            $table->string('file_number');
            $table->text('remarks')->nullable();

            // File number is unique only within the originating department.
            $table->unique(['department_id', 'file_number']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_records');
    }
};
