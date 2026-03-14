<?php

namespace Database\Seeders;

use App\Models\Ecourse\Course;
use App\Models\Ecourse\CourseModule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EcourseModuleSeeder extends Seeder
{
    public function run()
    {
        $courses = Course::published()->get();

        foreach ($courses as $course) {
            // Buat 6-10 modul per course
            for ($i = 1; $i <= rand(6, 10); $i++) {
                CourseModule::create([
                    'course_id' => $course->id,
                    'slug' => Str::slug($course->title . '-modul-' . $i),
                    'order_num' => $i,
                    'title' => "Modul {$i}: " . $this->getModuleTitle($course->level, $i),
                    'description' => "Deskripsi lengkap modul {$i} dari {$course->title}",
                    'video_path' => "ecourses/{$course->slug}/modul-{$i}.mp4",
                    'duration_minutes' => rand(15, 45),
                    'objectives' => [
                        "Memahami konsep dasar modul {$i}",
                        "Mampu menerapkan praktik modul {$i}",
                        "Mengetahui best practices modul {$i}"
                    ]
                ]);
            }
        }
    }

    private function getModuleTitle($level, $num)
    {
        $titles = [
            'pemula' => ['Pengantar Dasar', 'Konsep Utama', 'Praktik Sederhana'],
            'menengah' => ['Teknik Lanjutan', 'Analisis Mendalam', 'Implementasi'],
            'lanjut' => ['Strategi Expert', 'Optimasi Tinggi', 'Case Study']
        ];

        return $titles[$level][($num - 1) % 3] ?? 'Pelajaran Lanjutan';
    }
}
