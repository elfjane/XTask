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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('project');
            $table->string('title');
            $table->string('status'); // working, finish, in_progress, fail
            $table->string('confirm'); // Tentatively, Confirmed
            $table->date('deadline')->nullable();
            $table->date('scheduled_start')->nullable();
            $table->date('scheduled_end')->nullable();
            $table->date('actual_start')->nullable();
            $table->date('actual_finish')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
