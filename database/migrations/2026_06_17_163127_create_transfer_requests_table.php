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
        Schema::create('transfer_requests', function (Blueprint $table) {

            $table->id();

            $table->foreignId('file_id')
                ->constrained('file_records')
                ->onDelete('cascade');

            $table->foreignId('requested_by')
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('from_department')
                ->constrained('departments')
                ->onDelete('cascade');

            $table->foreignId('to_department')
                ->constrained('departments')
                ->onDelete('cascade');

            $table->foreignId('target_user')
                ->constrained('users')
                ->onDelete('cascade');

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_requests');
    }
};
