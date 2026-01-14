<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->index('status');
            $table->index('review_status');
            $table->index('project');
            $table->index('department');
            $table->index('level');
            $table->index('points');
            $table->index('release_date');
            $table->index('start_date');
            $table->index('expected_finish_date');
            $table->index('actual_finish_date');
            $table->index('approved_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['review_status']);
            $table->dropIndex(['project']);
            $table->dropIndex(['department']);
            $table->dropIndex(['level']);
            $table->dropIndex(['points']);
            $table->dropIndex(['release_date']);
            $table->dropIndex(['start_date']);
            $table->dropIndex(['expected_finish_date']);
            $table->dropIndex(['actual_finish_date']);
            $table->dropIndex(['approved_at']);
            $table->dropIndex(['created_at']);
        });
    }
};
