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
        Schema::create('file_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')
                ->constrained('file_records')
                ->onDelete('cascade');

            $table->foreignId('from_user')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('to_user')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('from_department')->nullable()
                ->constrained('departments')
                ->nullOnDelete();
            $table->foreignId('to_department')->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->string('action'); // created, transfer, approved, rejected
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_movements');
    }
};
