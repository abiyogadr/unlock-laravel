<?php

namespace Database\Seeders;

use App\Models\Ecourse\CourseCategory;
use Illuminate\Database\Seeder;

class EcourseCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Akuntansi Keuangan',
                'description' => 'Pelatihan lengkap akuntansi keuangan modern',
                'icon' => 'fa-calculator',
                'color' => '#3B82F6',
                'sort_order' => 1
            ],
            [
                'name' => 'Perpajakan',
                'description' => 'Update regulasi pajak terbaru Indonesia',
                'icon' => 'fa-file-invoice-dollar',
                'color' => '#F59E0B',
                'sort_order' => 2
            ],
            [
                'name' => 'Audit Internal',
                'description' => 'Teknik audit modern dan kepatuhan',
                'icon' => 'fa-search-magnifying-glass',
                'color' => '#10B981',
                'sort_order' => 3
            ],
            [
                'name' => 'Manajemen Keuangan',
                'description' => 'Strategi finansial dan budgeting',
                'icon' => 'fa-chart-line',
                'color' => '#8B5CF6',
                'sort_order' => 4
            ],
            [
                'name' => 'Pajak Digital',
                'description' => 'E-invoicing, e-Faktur, dan pajak online',
                'icon' => 'fa-laptop-code',
                'color' => '#EF4444',
                'sort_order' => 5
            ]
        ];

        foreach ($categories as $category) {
            CourseCategory::create($category);
        }
    }
}
