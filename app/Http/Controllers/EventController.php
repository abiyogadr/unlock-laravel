<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with('speakers');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['close', 'open']);
        }

        if ($request->filled('year')) {
            $query->whereYear('date_start', $request->year);
        }

        if ($request->filled('month')) {
            $query->whereMonth('date_start', $request->month);
        }

        // 2. Search (judul & speakers relationship)
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('event_title', 'like', '%' . $search . '%')
                ->orWhereHas('speakers', function ($query) use ($search) {
                    $query->where('speakers.speaker_name', 'like', '%' . $search . '%');
                });
            });
        }

        $events = $query
            ->orderBy('date_start', 'desc')
            ->paginate(9)
            ->withQueryString();

        return view('event.index', compact('events'));
    }

    public function show($event_code)
    {
        $event = Event::where('event_code', $event_code)->firstOrFail();

        return view('event.show', compact('event'));
    }
}
