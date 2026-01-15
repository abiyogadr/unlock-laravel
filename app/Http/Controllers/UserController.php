<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    
    public function profile()
    {
        $cities_provinces = Area::selectRaw("areas.kode, CONCAT(areas.nama, ' - ', provinces.nama) as nama_lengkap")
            ->join('areas as provinces', function($join) {
                $join->on(DB::raw('LEFT(areas.kode, 2)'), '=', 'provinces.kode')
                    ->whereRaw('LENGTH(provinces.kode) = 2');
            })
            ->whereRaw('LENGTH(areas.kode) = 5')
            ->orderBy('nama_lengkap')
            ->get();
        $professions = ['Mahasiswa', 'Profesional', 'Wirausaha', 'Pegawai Pemerintah'];
        
        return view('user.profile', compact('cities_provinces', 'professions'));
    }

    public function profileUpdate(Request $request)
    {
        $user = Auth::user();

        $whatsapp = $request->input('whatsapp');
        if ($whatsapp) {
            $cleanNumber = preg_replace('/[^0-9]/', '', $whatsapp);
            
            $position = strpos($cleanNumber, '8');
            
            if ($position !== false) {
                $whatsapp = substr($cleanNumber, $position);  // Mulai dari 8
            } else {
                $whatsapp = $cleanNumber;  // Nomor tanpa 8 (nomor rumah)
            }
            
            $request->merge(['whatsapp' => $whatsapp]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:6|max:20|alpha_dash:ascii|unique:users,username,' . $user->id,
            'whatsapp' => 'required|string|max:14|unique:users,phone,' . $user->id,
            'email' => 'required|string|email:rfc,dns|max:255|unique:users,email,' . $user->id,
            'gender' => 'required|in:male,female',
            'birth_place' => 'required|string|max:100',
            'birth_date' => 'required|date',
            'city' => 'required|string|max:100',
            'province' => 'nullable|string|max:100',
            'job' => 'required|string|max:100',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'instagram' => 'nullable|string|max:100',
            'linkedin' => 'nullable|string|max:100',
        ], [
            'username.unique' => 'Username sudah digunakan, coba yang lain.',
            'username.min'    => 'Username terlalu pendek.',
            'whatsapp.unique' => 'Nomor WhatsApp sudah terdaftar.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, dash (-), dan underscore (_).',
            'email.unique' => 'Email sudah terdaftar, gunakan email lain.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'whatsapp.required' => 'No. WhatsApp wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'birth_place.required' => 'Tempat lahir wajib diisi.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'city.required' => 'Kota Domisili wajib diisi.',
            'job.required' => 'Pekerjaan wajib diisi.',
            'avatar' => 'Gagal mengupload foto',
        ]);

        if (isset($validated['whatsapp'])) {
            $validated['phone'] = $validated['whatsapp'];  // whatsapp → phone
        }

        // Trim nama kabupaten (hapus " - Provinsi")
        if (!empty($validated['city'])) {
            [$city, $province] = array_map(
                'trim',
                explode('-', $validated['city'], 2)
            );

            $validated['city'] = $city;
            $validated['province'] = $province ?? '';
        }

        if ($validated['birth_place'] ?? false) {
            $validated['birth_place'] = trim(explode('-', $validated['birth_place'])[0]);
        }

        // Handle upload avatar
        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika ada
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            /**IMAGE COMPRESSION */
            $manager = new ImageManager(new Driver());
            $image = $manager
                ->read($request->file('avatar'))
                ->scaleDown(512)
                ->toJpeg(65); 
            $path = 'avatars/' . uniqid() . '.jpg';
            Storage::disk('public')->put($path, $image);
            $validated['avatar'] = $path;
        }

        // Update user
        $user->update($validated);

        $redirectTo = session()->pull('url.intended', route('profile'));

        return redirect($redirectTo)->with('success', 'Profil berhasil diperbarui!');
    }

    public function myEvents()
    {
        $registrations = Registration::with(['event', 'packet'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('user.events', compact('registrations'));
    }

    public function show($id)
    {
        // 1. Cari data pendaftaran berdasarkan ID (atau UUID)
        // Eager load relasi yang dibutuhkan di view: Event, Packet, dan Proofs (bukti upload)
        $registration = Registration::with(['event', 'packet', 'uploadproofs'])
            ->findOrFail($id);

        // 2. KEAMANAN (Authorization Check)
        // Pastikan yang membuka halaman ini adalah pemilik pendaftaran itu sendiri.
        if ($registration->user_id != Auth::id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.'); // Tampilkan error 403 Forbidden
        }

        // 3. Kirim ke View
        return view('user.detail-event', compact('registration'));
    }

}
