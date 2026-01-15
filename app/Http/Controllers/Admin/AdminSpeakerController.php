<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Speaker;
use Illuminate\Http\Request;

class AdminSpeakerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $query = Speaker::latest();

        // Fitur Pencarian (Nama, Email, Perusahaan, atau Kode)
        if ($request->filled('search')) {
            $query->where(function($q) use ($search) {
                $q->where('speaker_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('speaker_code', 'like', "%{$search}%");
            });
        }

        $speakers = $query->paginate(10)->withQueryString();

        return view('admin.speakers.index', compact('speakers'));
    }

    public function create()
    {
        return view('admin.speakers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'speaker_code' => 'RS',
            'speaker_name' => 'required|string|max:255',
            'prefix_title' => 'nullable|string|max:50',
            'suffix_title' => 'nullable|string|max:50',
            'email'        => 'nullable|email|unique:speakers,email',
            'phone'        => 'nullable|string|max:20',
            'position'     => 'nullable|string|max:255',
            'company'      => 'nullable|string|max:255',
        ]);

        $validated['speaker_name'] = ucwords(strtolower($request->speaker_name));
        
        $lastSpeaker = Speaker::orderBy('id', 'desc')->first();
        $lastNumber = 0;

        if ($lastSpeaker) {
            $lastNumber = (int) substr($lastSpeaker->speaker_code, 2);
        }

        $nextCode = 'RS' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        $validated['speaker_code'] = $nextCode;

        Speaker::create($validated);

        return redirect()->route('admin.speakers.index')
            ->with('success', 'Speaker berhasil ditambahkan.');
    }

    public function edit(Speaker $speaker)
    {
        return view('admin.speakers.edit', compact('speaker'));
    }

    public function update(Request $request, Speaker $speaker)
    {
        $validated = $request->validate([
            'speaker_name' => 'required|string|max:255',
            'prefix_title' => 'nullable|string|max:50',
            'suffix_title' => 'nullable|string|max:50',
            'email'        => 'nullable|email|unique:speakers,email,' . $speaker->id,
            'phone'        => 'nullable|string|max:20',
            'position'     => 'nullable|string|max:255',
            'company'      => 'nullable|string|max:255',
        ]);

        $speaker->update($validated);

        return redirect()->route('admin.speakers.index')
            ->with('success', 'Data speaker berhasil diperbarui.');
    }

    public function destroy(Speaker $speaker)
    {
        // Cek apakah speaker sedang digunakan di event tertentu
        if ($speaker->events()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus speaker yang masih terdaftar di event.');
        }

        $speaker->delete();

        return redirect()->route('admin.speakers.index')
            ->with('success', 'Speaker berhasil dihapus.');
    }
}
