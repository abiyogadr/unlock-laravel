<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Packet;
use Illuminate\Http\Request;

class AdminPacketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil parameter filter
        $status = $request->get('status', 'all');
        $search = $request->get('search');

        $query = Packet::query()->orderBy('id', 'desc');

        // Filter berdasarkan Pencarian Nama Paket
        if ($request->filled('search')) {
            $query->where('packet_name', 'like', '%' . $search . '%');
        }

        // Filter berdasarkan Status (Aktif/Non-Aktif)
        if ($status !== 'all') {
            // active -> 1, inactive -> 0
            $isActive = ($status === 'active') ? 1 : 0;
            $query->where('is_active', $isActive);
        }

        // Pagination dengan mempertahankan query string (search & status) di link halaman
        $packets = $query->paginate(10)->withQueryString();

        return view('admin.packets.index', compact('packets', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Jika ada data lain yang perlu dikirim (misal Event untuk relasi), tambahkan di sini
        return view('admin.packets.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'packet_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean', // 1 atau 0
            
            // Validasi Requirements (JSON)
            // Format: array dengan key boolean (true/false)
            'requirements' => 'nullable|array',
            'requirements.follow_ig' => 'nullable|boolean',
            'requirements.follow_yt' => 'nullable|boolean',
            'requirements.follow_tt' => 'nullable|boolean',
            'requirements.tag_friends' => 'nullable|boolean',
            'requirements.repost_story' => 'nullable|boolean',
            'requirements.repost_groups' => 'nullable|boolean',
            'requirements.repost_wa_story' => 'nullable|boolean',
        ]);

        // 2. Format Requirements untuk disimpan sebagai JSON
        // Pastikan di Model Packet ada: protected $casts = ['requirements' => 'array'];
        $requirements = [
            'follow_ig'       => $request->boolean('requirements.follow_ig'),
            'follow_yt'       => $request->boolean('requirements.follow_yt'),
            'follow_tt'       => $request->boolean('requirements.follow_tt'),
            'tag_friends'     => $request->boolean('requirements.tag_friends'),
            'repost_story'    => $request->boolean('requirements.repost_story'),
            'repost_groups'   => $request->boolean('requirements.repost_groups'),
            'repost_wa_story' => $request->boolean('requirements.repost_wa_story'),
        ];
        
        $validated['requirements'] = $requirements;

        // 3. Simpan Data
        Packet::create($validated);

        return redirect()
            ->route('admin.packets.index')
            ->with('success', 'Paket berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Packet $packet)
    {
        return view('admin.packets.edit', compact('packet'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Packet $packet)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'packet_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
            
            'requirements' => 'nullable|array',
            'requirements.*' => 'nullable|boolean',
        ]);

        // 2. Format Requirements
        $requirements = [
            'follow_ig'       => $request->boolean('requirements.follow_ig'),
            'follow_yt'       => $request->boolean('requirements.follow_yt'),
            'follow_tt'       => $request->boolean('requirements.follow_tt'),
            'tag_friends'     => $request->boolean('requirements.tag_friends'),
            'repost_story'    => $request->boolean('requirements.repost_story'),
            'repost_groups'   => $request->boolean('requirements.repost_groups'),
            'repost_wa_story' => $request->boolean('requirements.repost_wa_story'),
        ];
        $validated['requirements'] = $requirements;

        // 3. Update Data
        $packet->update($validated);

        return back()->with('success', 'Paket berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Packet $packet)
    {
        // Opsional: Cek apakah paket sedang dipakai event
        // if ($packet->events()->count() > 0) {
        //     return back()->with('error', 'Gagal hapus: Paket ini masih terhubung dengan event.');
        // }

        $packet->delete();

        return redirect()
            ->route('admin.packets.index')
            ->with('success', 'Paket berhasil dihapus');
    }
}
