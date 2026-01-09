<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(DepartmentsSeeder::class);
        $this->call(ProjectsSeeder::class);

        $admin = \App\Models\User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => \App\Models\User::ROLE_ADMIN,
                'is_active' => true,
                'department_id' => 1
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => \App\Models\User::ROLE_MANAGER,
                'is_active' => true,
                'department_id' => 1
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'task@example.com'],
            [
                'name' => 'Task User',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => \App\Models\User::ROLE_TASK_USER,
                'is_active' => true,
                'department_id' => 1
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'executor@example.com'],
            [
                'name' => 'Executor User',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => \App\Models\User::ROLE_EXECUTOR,
                'is_active' => true,
                'department_id' => 1
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'auditor@example.com'],
            [
                'name' => 'Auditor User',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => \App\Models\User::ROLE_AUDITOR,
                'is_active' => true,
                'department_id' => 1
            ]
        );
    }
}
