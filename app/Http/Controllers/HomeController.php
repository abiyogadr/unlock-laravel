<?php

namespace App\Http\Controllers;

use App\Models\Event;

class HomeController extends Controller
{
    public function index()
    {
        $events = Event::with('speakers')
            ->whereIn('status', ['open', 'close'])
            ->orderBy('date_start', 'desc')
            ->limit(6)
            ->get();

        return view('welcome', compact('events'));
    }
}
