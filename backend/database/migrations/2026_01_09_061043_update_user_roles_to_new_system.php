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
        // Update existing 'user' roles to 'executor'
        \Illuminate\Support\Facades\DB::table('users')
            ->where('role', 'user')
            ->update(['role' => \App\Models\User::ROLE_EXECUTOR]);

        // Change default value for future users
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default(\App\Models\User::ROLE_EXECUTOR)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->change();
        });
    }
};
