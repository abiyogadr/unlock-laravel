<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;


class AuthController extends Controller
{
    public function show(Request $request)
    {
        if ($request->filled('redirect')) {
            session(['url.intended' => $request->redirect]);
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required'],
            'password' => ['required'],
            'redirect' => ['nullable', 'string'],
        ]);

        // Deteksi tipe login (email, username, atau phone)
        $loginField = $this->getLoginField($credentials['login']);

        $loginValue = $credentials['login'];

        // --- LOGIKA UNIVERSAL PHONE ---
        if ($loginField === 'phone') {
            $cleanNumber = preg_replace('/[^0-9]/', '', $loginValue);

            $position = strpos($cleanNumber, '8');

            if ($position !== false) {
                $loginValue = substr($cleanNumber, $position);
            } else {
                $loginValue = $cleanNumber;
            }
        }

        if (Auth::attempt([
            $loginField => $loginValue,
            'password' => $credentials['password']
            ], $request->boolean('remember'))) {
            
            $request->session()->regenerate();
            $user = Auth::user();
            $requiredFields = [
                'name' => 'Nama Lengkap',
                'username' => 'Username', 
                'phone' => 'No. WhatsApp',
                'email' => 'Email',
                'gender' => 'Jenis Kelamin',
                'birth_place' => 'Tempat Lahir',
                'birth_date' => 'Tanggal Lahir',
                'city' => 'Kabupaten Domisili',
                'province' => 'Provinsi',
                'job' => 'Pekerjaan'
            ];
            $incompleteFields = [];
            foreach ($requiredFields as $field => $label) {
                if (empty($user->$field)) {
                    $incompleteFields[] = $label;
                }
            }
            if (!empty($incompleteFields)) {
                if ($request->filled('redirect')) {
                    session(['url.intended' => $request->redirect]);
                } 
                elseif (!session()->has('url.intended')) {
                    session(['url.intended' => url()->previous()]);
                }

                return redirect()->route('profile')
                    ->with('warning', 'Lengkapi data profil kamu');
            }

            if (!$request->user()->hasVerifiedEmail()) {
                if ($request->filled('redirect')) {
                    session(['url.intended' => $request->redirect]);
                }

                return redirect()->route('profile')
                    ->with('info', 'Silakan verifikasi email Anda untuk mendapatkan akses penuh.');
            }
            
            return redirect()->intended('/');
        }

        return back()->with('error', 'Email, username, atau nomor HP tidak ditemukan.')
            ->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    /**
     * Tentukan field yang dipakai untuk login
     */
    private function getLoginField(string $login): string
    {
        // 1. Cek apakah itu email
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        // 2. Cek apakah itu nomor telepon (hanya angka, spasi, -, +)
        if (preg_match('/^[0-9\s\-\+]+$/', $login)) {
            return 'phone';
        }

        // 3. Default adalah username
        return 'username';
    }

    public function showSignUpForm()
    {
        return view('auth.signup');
    }

    public function signup(Request $request)
    {
        if ($request->has('redirect')) {
            session(['url.intended' => $request->query('redirect')]);
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|min:6|max:20|alpha_dash:ascii|unique:users,username',
            'whatsapp' => 'required|string|max:14|unique:users,phone',
            'email'    => 'required|string|email:rfc,dns|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ], [
            'username.unique' => 'Username sudah digunakan.',
            'username.min'    => 'Username terlalu pendek.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, dash (-), dan underscore (_).',
            'whatsapp.unique' => 'Nomor WhatsApp sudah terdaftar, silakan login.',
            'email.unique'    => 'Email sudah terdaftar, silakan login.',
            'required'        => 'Kolom :attribute wajib diisi.',
            'email.email'     => 'Format email tidak valid.',
            'password.min'    => 'Password minimal :min karakter.',
        ]);

        if ($validated['whatsapp']) {
            $cleanNumber = preg_replace('/[^0-9]/', '', $validated['whatsapp']);

            $position = strpos($cleanNumber, '8');

            if ($position !== false) {
                $validated['whatsapp'] = substr($cleanNumber, $position);
            } else {
                $validated['whatsapp'] = $cleanNumber;
            }
        }

        $user = User::create([
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'phone'    => $validated['whatsapp'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }

}   
