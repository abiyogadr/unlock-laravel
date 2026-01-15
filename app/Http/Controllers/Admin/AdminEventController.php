<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Speaker;
use App\Models\Packet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminEventController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Input
        $search = $request->get('search');
        $status = $request->get('status', 'all');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // 2. Base Query
        $query = Event::orderBy('date_start', 'desc');

        // 3. Filter Search (Kode atau Judul)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('event_code', 'like', "%{$search}%")
                ->orWhere('event_title', 'like', "%{$search}%");
            });
        }

        // 4. Filter Status
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // 5. Filter Range Tanggal
        if ($dateFrom) {
            $query->whereDate('date_start', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('date_start', '<=', $dateTo);
        }

        // 6. Paginate & Pertahankan Query String
        $events = $query->paginate(10)->withQueryString();

        return view('admin.events.index', compact('events', 'status', 'search', 'dateFrom', 'dateTo'));
    }

    public function create()
    {
        $speakers = Speaker::select('id', 'prefix_title', 'speaker_name', 'suffix_title')
        ->get()
        ->map(fn($s) => [
            'id' => $s->id,
            'display_name' => trim(
                ($s->prefix_title ? $s->prefix_title . ' ' : '') . 
                $s->speaker_name . 
                ($s->suffix_title ? ' ' . $s->suffix_title : '')
            )
        ]);
        $packets = Packet::where('is_active', 1)
                    ->select('id', 'packet_name')
                    ->get();
        return view('admin.events.create', compact('speakers', 'packets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_code' => 'required|unique:events,event_code',
            'event_title' => 'required|string',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'time_start' => 'required',
            'time_end' => 'required',
            'classification' => 'nullable|string',
            'collaboration' => 'nullable|string',
            'status' => 'required|in:open,close,draft',
            'payment_unique_code' => 'nullable|string',

            'speaker1_id' => 'nullable|exists:speakers,id',
            'speaker2_id' => 'nullable|exists:speakers,id',
            'speaker3_id' => 'nullable|exists:speakers,id',

            'packet_ids' => 'nullable|array',
            'packet_ids.*' => 'exists:packets,id',

            'kv_path' => 'nullable|image|max:2048',
        ]);
        
        $validated['event_code'] = 'event-' . $validated['event_code'];
        $date = \Carbon\Carbon::parse($validated['date_start']);
        $validated['year'] = $date->year;
        $validated['month'] = $date->month;

        if ($request->hasFile('kv_path')) {
            $validated['kv_path'] = $request->file('kv_path')
                ->store('events', 'public');
        }

        $event = Event::create($validated);

        if ($request->has('speaker_ids')) {
            $syncData = [];
            foreach ($request->speaker_ids as $speakerId) {
                $speaker = Speaker::find($speakerId);
                if ($speaker) {
                    $syncData[$speakerId] = [
                        'event_code' => $event->event_code,
                        'speaker_code' => $speaker->speaker_code,
                        'speaker_name' => $speaker->speaker_name,
                    ];
                }
            }
            $event->speakers()->sync($syncData);
        } else {
            $event->speakers()->detach();
        }
        
        // Handle packets
        if ($request->has('packet_ids')) {

            $syncData = [];

            foreach ($request->packet_ids as $packetId) {
                $packet = Packet::find($packetId);

                $syncData[$packetId] = [
                    'event_code'  => $event->event_code,
                    'packet_name' => $packet->packet_name,
                ];
            }

            $event->packets()->sync($syncData);
        }

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event berhasil ditambahkan');
    }

    public function edit(Event $event)
    {   
        $eventSpeakers = $event->speakers()->select('speakers.id', 'speakers.speaker_name')->get()->values();
        $speakers = Speaker::select('id', 'prefix_title', 'speaker_name', 'suffix_title')
        ->get()
        ->map(fn($s) => [
            'id' => $s->id,
            'display_name' => trim(
                ($s->prefix_title ? $s->prefix_title . ' ' : '') . 
                $s->speaker_name . 
                ($s->suffix_title ? ' ' . $s->suffix_title : '')
            )
        ]);

        $packets = Packet::where('is_active', '1')
                        ->select('id', 'packet_name')
                        ->get();
        
        $eventPackets = $event->packets()->select('packets.id', 'packets.packet_name')->get();
        return view('admin.events.edit', compact('event', 'speakers', 'eventSpeakers', 'packets', 'eventPackets'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'event_code' => 'required|unique:events,event_code,' . $event->id,
            'event_title' => 'required|string',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'time_start' => 'required',
            'time_end' => 'required',
            'classification' => 'nullable|string',
            'collaboration' => 'nullable|string',
            'status' => 'required|in:open,close,draft',
            'payment_unique_code' => 'nullable|string',
            'speaker1_id' => 'nullable|exists:speakers,id',
            'speaker2_id' => 'nullable|exists:speakers,id',
            'speaker3_id' => 'nullable|exists:speakers,id',
            'kv_path' => 'nullable|image|max:2048',
            'packet_ids' => 'nullable|array',
            'packet_ids.*' => 'exists:packets,id',
        ]);

        if ($request->hasFile('kv_path')) {
            if ($event->kv_path) {
                Storage::disk('public')->delete($event->kv_path);
            }
            $validated['kv_path'] = $request->file('kv_path')->store('events/kv', 'public');
        }

        $event->update($validated);

        if ($request->has('speaker_ids')) {
            $syncData = [];
            foreach ($request->speaker_ids as $speakerId) {
                $speaker = Speaker::find($speakerId);
                if ($speaker) {
                    $syncData[$speakerId] = [
                        'event_code' => $event->event_code,
                        'speaker_code' => $speaker->speaker_code,
                        'speaker_name' => $speaker->speaker_name,
                    ];
                }
            }
            $event->speakers()->sync($syncData);
        } else {
            $event->speakers()->detach();
        }

        // Handle packets
        if ($request->has('packet_ids')) {

            $syncData = [];

            foreach ($request->packet_ids as $packetId) {
                $packet = Packet::find($packetId);

                $syncData[$packetId] = [
                    'event_code'  => $event->event_code,
                    'packet_name' => $packet->packet_name,
                ];
            }

            $event->packets()->sync($syncData);
        } else {
            $event->packets()->detach();
        }

        return back()->with('success', 'Event berhasil diupdate');

    }

    public function destroy(Event $event)
    {
        try {
            if ($event->registrations()->exists()) {
                return back()->with('error', 'Gagal: Event ini sudah memiliki pendaftar. Hapus data pendaftar terlebih dahulu.');
            }
           $event->packets()->detach(); 

            $imagePath = $event->kv_path;

            $event->delete();

            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return redirect()->route('admin.events.index')
                ->with('success', 'Event berhasil dihapus');

        } catch (\Exception $e) {

            return back()->with('error', 'Terjadi kesalahan saat menghapus event.');
            // dd($e->getMessage()); 
        }
    }

    public function toggleAttendance(Event $event)
    {
        $event->update([
            'is_attendance_open' => !$event->is_attendance_open
        ]);

        return response()->json([
            'success' => true,
            'is_open' => (bool) $event->is_attendance_open, 
        ]);
    }

}
