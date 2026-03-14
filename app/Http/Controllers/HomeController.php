<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ecourse\CourseCategory;

class HomeController extends Controller
{
    public function index()
    {
        $events = Event::with('speakers')
            ->whereIn('status', ['open', 'close'])
            ->orderBy('date_start', 'desc')
            ->limit(6)
            ->get();

        // Load active e-course categories to show on homepage
        $categories = CourseCategory::active()->get();

        return view('welcome', compact('events', 'categories'));
    }
}
