<?php

namespace Database\Seeders;

use App\Models\Ecourse\Course;
use App\Models\Ecourse\CourseCategory;
use App\Models\Speaker; // Sesuai perubahan instructor → speaker
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EcourseCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil speakers & categories yang sudah ada
        $categories = CourseCategory::active()->get();
        $speakers = Speaker::take(5)->get();
        
        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Run EcourseCategorySeeder first!');
            return;
        }

        if ($speakers->isEmpty()) {
            $this->command->warn('No speakers found. Create speakers first!');
            return;
        }

        $coursesData = [
            [
                'title' => 'Dasar Akuntansi Keuangan 2026',
                'slug' => 'dasar-akuntansi-keuangan-2026',
                'short_description' => 'Pelajari dasar-dasar akuntansi keuangan sesuai PSAK terbaru',
                'description' => 'Kursus lengkap untuk pemula akuntansi dengan praktik nyata menggunakan software akuntansi terkini.',
                'duration_minutes' => 480, // 8 jam
                'level' => 'pemula',
                'is_free' => false,
                'price' => 299000,
                'status' => 'published',
                'speaker_id' => $speakers->random()->id,
                'thumbnail_path' => 'ecourses/thumbnails/akuntansi.jpg',
                'kv_path' => 'ecourses/kv/akuntansi-hero.jpg'
            ],
            [
                'title' => 'SPT Tahunan Pribadi & Badan 2026 GRATIS',
                'slug' => 'spt-tahunan-2026-gratis',
                'short_description' => 'Cara lengkap lapor SPT Tahunan e-Filing 2026',
                'description' => 'Panduan praktis dari nol sampai selesai lapor SPT Tahunan Pribadi & Badan.',
                'duration_minutes' => 180, // 3 jam
                'level' => 'pemula',
                'is_free' => true,
                'price' => 0,
                'status' => 'published',
                'speaker_id' => $speakers->random()->id,
                'thumbnail_path' => 'ecourses/thumbnails/spt-tahunan.jpg',
                'kv_path' => 'ecourses/kv/spt-hero.jpg'
            ],
            [
                'title' => 'Audit Internal Berbasis Teknologi AI',
                'slug' => 'audit-internal-ai-2026',
                'short_description' => 'Audit modern dengan AI dan data analytics',
                'description' => 'Teknik audit terkini menggunakan teknologi AI, machine learning, dan big data.',
                'duration_minutes' => 720, // 12 jam
                'level' => 'lanjut',
                'is_free' => false,
                'price' => 599000,
                'status' => 'published',
                'speaker_id' => $speakers->random()->id,
                'thumbnail_path' => 'ecourses/thumbnails/audit-ai.jpg',
                'kv_path' => 'ecourses/kv/audit-hero.jpg'
            ],
            [
                'title' => 'Manajemen Keuangan Perusahaan',
                'slug' => 'manajemen-keuangan-perusahaan',
                'short_description' => 'Strategi budgeting dan cash flow management',
                'description' => 'Belajar strategi keuangan perusahaan dari praktisi berpengalaman.',
                'duration_minutes' => 360, // 6 jam
                'level' => 'menengah',
                'is_free' => false,
                'price' => 399000,
                'status' => 'published',
                'speaker_id' => $speakers->random()->id,
                'thumbnail_path' => 'ecourses/thumbnails/manajemen.jpg',
                'kv_path' => 'ecourses/kv/manajemen-hero.jpg'
            ]
        ];

        foreach ($coursesData as $courseData) {
            // **ANTI-DUPLIKASI: firstOrCreate berdasarkan slug**
            $course = Course::firstOrCreate(
                ['slug' => $courseData['slug']],
                $courseData
            );

            // **FIX PIVOT: Attach kategori dengan ID array**
            $categoryIds = $categories
                ->where('is_active', true)
                ->random(rand(1, 2))
                ->pluck('id')
                ->toArray();

            // Sync kategori (ganti semua relasi, aman)
            $course->categories()->sync($categoryIds);

            $this->command->info("✅ Created/Updated: {$course->title}");
        }

        $this->command->info('🎉 EcourseCourseSeeder completed!');
    }
}
