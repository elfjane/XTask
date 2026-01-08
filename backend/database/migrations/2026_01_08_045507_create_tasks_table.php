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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->integer('level'); // 1: Ordinary, 2: Important, 3: Priority
            $table->string('status'); // working, in progress, cancelled, idle, waiting qa, finished, miss
            $table->foreignId('user_id')->constrained(); // Assignee
            $table->string('project');
            $table->string('department');
            $table->string('work');
            $table->decimal('points', 4, 1);
            $table->date('release_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expected_finish_date')->nullable();
            $table->date('actual_finish_date')->nullable();
            $table->string('output_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
