<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('file_id')
                ->constrained('file_records')
                ->onDelete('cascade');

            $table->foreignId('sender_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Nullable: when a file is transferred to a department (not a specific user),
            // receiver_id is NULL until the department admin assigns it.
            $table->foreignId('receiver_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('transferred_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_transfers');
    }
};
