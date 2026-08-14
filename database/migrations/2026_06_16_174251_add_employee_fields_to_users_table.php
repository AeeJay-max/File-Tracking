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
        Schema::table('users', function (Blueprint $table) {

            $table->foreignId('department_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('designation_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('employee_code')
                ->nullable()
                ->unique();

            $table->string('phone', 20)
                ->nullable();

            $table->boolean('is_active')
                ->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('designation_id');

            $table->dropColumn([
                'employee_code',
                'phone',
                'is_active',
            ]);
        });
    }
};
