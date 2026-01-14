<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = ['技術', '營運', '產品', '美術', '客服', '測試', '前端', '後端', '運維', '行政', '人事', '財務', '行銷', '業務', '企劃', '管理'];

        foreach ($departments as $name) {
            \App\Models\Department::firstOrCreate(['name' => $name]);
        }
    }
}
