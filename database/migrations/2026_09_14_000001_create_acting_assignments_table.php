<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acting_assignments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('file_id')->constrained('file_records')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
            $table->string('authority_type')->default('acting_permsec');
            $table->text('instructions');
            $table->text('required_action')->nullable();
            $table->text('next_step')->nullable();
            $table->enum('return_destination', ['records', 'permsec_office'])->default('records');
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled', 'expired'])->default('active');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('acted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['file_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acting_assignments');
    }
};
