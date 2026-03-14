<?php

namespace Database\Seeders;

use App\Models\Ecourse\Course;
use App\Models\Ecourse\UserCourse;
use App\Models\User;
use Illuminate\Database\Seeder;

class EcourseUserCourseSeeder extends Seeder
{
    public function run()
    {
        $users = User::take(5)->get();
        $courses = Course::published()->get();

        foreach ($users as $user) {
            foreach ($courses->random(rand(2, 4)) as $course) {
                // Skip jika sudah ada
                if ($user->ecourses()->where('course_id', $course->id)->exists()) {
                    continue;
                }

                $totalModules = $course->modules()->count();
                
                UserCourse::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'total_modules' => $totalModules,
                    'completed_modules' => rand(0, $totalModules),
                    'progress' => rand(10, 100),
                    'status' => rand(0, 30) < 10 ? 'completed' : 'in_progress'
                ]);
            }
        }
    }
}
