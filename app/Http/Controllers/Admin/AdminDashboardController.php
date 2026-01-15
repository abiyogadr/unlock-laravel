<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Upanel;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total_events' => Event::count(),
            'active_events' => Event::where('status', 'active')->count(),
            'total_registrations' => Registration::count(),
            'total_admins' => Upanel::count(),
        ];

        // Filter dan pagination events
        $status = $request->get('status', 'all');
        $query = Event::query()->orderBy('date_start', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $events = $query->paginate(5)->withQueryString();

        return view('admin.dashboard', compact('stats', 'events', 'status'));
    }
}
