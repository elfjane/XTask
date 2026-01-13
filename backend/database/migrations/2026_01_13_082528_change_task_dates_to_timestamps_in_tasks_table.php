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
            $table->timestamp('release_date')->nullable()->change();
            $table->timestamp('start_date')->nullable()->change();
            $table->timestamp('expected_finish_date')->nullable()->change();
            $table->timestamp('actual_finish_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->date('release_date')->nullable()->change();
            $table->date('start_date')->nullable()->change();
            $table->date('expected_finish_date')->nullable()->change();
            $table->date('actual_finish_date')->nullable()->change();
        });
    }
};
