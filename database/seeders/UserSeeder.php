<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Upanel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Hapus data lama (opsional, untuk development)
        Upanel::truncate();
        User::where('username', 'abiyogadr')->delete();

        // **CARA 1: Langsung dan Simple (REKOMENDASI) **
        $admin = User::create([
            'name' => 'Abi Yoga Dwi Raharjo',
            'username' => 'abiyogadr',
            'email' => 'abiyogadr@gmail.com',
            'password' => Hash::make('unlock2025'), // Gunakan Hash::make()
        ]);

        // Buat record admin dengan model (auto-sync username & full_name)
        Upanel::create([
            'user_id' => $admin->id,
            // Kolom username & full_name akan otomatis terisi dari boot() model
        ]);

        // ** CARA 3: Menggunakan Relationship (Paling Clean) **
        $admin3 = User::create([
            'name' => 'Admin 2',
            'username' => 'admin2',
            'email' => 'admin2@unlock.com',
            'password' => Hash::make('unlock2025'),
        ]);

        // Jika sudah buat relasi di model User
        $admin3->upanel()->create([
            // user_id otomatis terisi dari $admin3->id
        ]);

        // Output untuk konfirmasi
        $this->command->info('✅ Admin users created successfully!');
        $this->command->table(
            ['ID', 'Username', 'Email', 'Admin?'],
            User::with('upanel')->get()->map(function ($user) {
                return [
                    $user->id,
                    $user->username,
                    $user->email,
                    $user->upanel ? '✅ Yes' : '❌ No',
                ];
            })->toArray()
        );
    }
}
