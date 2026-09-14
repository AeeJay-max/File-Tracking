<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('file_records', function (Blueprint $table) {
            $table->index(['current_department_id', 'status'], 'idx_file_rec_dept_status');
            $table->index(['department_id', 'file_number'], 'idx_file_rec_origin_num');
            $table->index('created_by', 'idx_file_rec_creator');
            $table->index('current_user_id', 'idx_file_rec_holder');
            $table->index('completed_at', 'idx_file_rec_completed');
        });

        Schema::table('file_movements', function (Blueprint $table) {
            $table->index(['file_id', 'created_at'], 'idx_move_file_created');
            $table->index(['from_department', 'to_department'], 'idx_move_depts');
            $table->index(['from_user', 'to_user'], 'idx_move_users');
        });

        Schema::table('file_transfers', function (Blueprint $table) {
            $table->index(['file_id', 'transferred_at'], 'idx_trans_file_date');
            $table->index(['sender_id', 'receiver_id'], 'idx_trans_users');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['department_id', 'is_active', 'role'], 'idx_users_dept_active_role');
        });
    }

    public function down(): void
    {
        Schema::table('file_records', function (Blueprint $table) {
            $table->dropIndex('idx_file_rec_dept_status');
            $table->dropIndex('idx_file_rec_origin_num');
            $table->dropIndex('idx_file_rec_creator');
            $table->dropIndex('idx_file_rec_holder');
            $table->dropIndex('idx_file_rec_completed');
        });

        Schema::table('file_movements', function (Blueprint $table) {
            $table->dropIndex('idx_move_file_created');
            $table->dropIndex('idx_move_depts');
            $table->dropIndex('idx_move_users');
        });

        Schema::table('file_transfers', function (Blueprint $table) {
            $table->dropIndex('idx_trans_file_date');
            $table->dropIndex('idx_trans_users');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_dept_active_role');
        });
    }
};
