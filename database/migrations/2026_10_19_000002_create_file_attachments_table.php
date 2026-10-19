<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('file_attachments')) {
            Schema::create('file_attachments', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('file_record_id')->constrained('file_records')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('attachment_path');
                $table->string('attachment_name');
                $table->string('attachment_mime')->nullable();
                $table->unsignedInteger('version')->default(1);
                $table->string('version_label')->default('Original File');
                $table->boolean('is_latest')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();
            });

            // Backfill existing attachments
            $existing = DB::table('file_records')->whereNotNull('attachment_path')->get();
            foreach ($existing as $f) {
                DB::table('file_attachments')->insert([
                    'uuid' => (string) Str::uuid(),
                    'file_record_id' => $f->id,
                    'user_id' => $f->created_by,
                    'attachment_path' => $f->attachment_path,
                    'attachment_name' => $f->attachment_name ?: 'Attachment',
                    'attachment_mime' => $f->attachment_mime,
                    'version' => 1,
                    'version_label' => 'Original File',
                    'is_latest' => true,
                    'created_at' => $f->created_at ?? now(),
                    'updated_at' => $f->updated_at ?? now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('file_attachments');
    }
};
