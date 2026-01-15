<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Hanya untuk user baru: download avatar sekali saja
                $localAvatarPath = null;
                $avatarUrl = $googleUser->getAvatar();

                if ($avatarUrl) {
                    try {
                        $response = Http::get($avatarUrl);
                        if ($response->successful()) {
                            $filename = 'avatars/'.uniqid().'.jpg';
                            Storage::disk('public')->put($filename, $response->body());
                            $localAvatarPath = $filename;
                        }
                    } catch (Exception $e) {
                        // Log::warning('Gagal download avatar Google untuk user baru: '.$e->getMessage());
                    }
                }
                
                $user = User::create([
                    'google_id' => $googleUser->getId(),
                    'name'      => $googleUser->getName(),
                    'email'     => $googleUser->getEmail(),
                    'avatar'    => $localAvatarPath,
                ]);
                
            } else {
                if (is_null($user->google_id)) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'name'      => $googleUser->getName(),
                    ]);
                }
            }

            Auth::login($user, true);

            return redirect()->intended(route('home'))
                ->with('success', 'Selamat datang kembali!');

        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::warning('Google state mismatch: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', '⚠️ Login Google gagal. Refresh halaman lalu coba lagi.');
        } catch (Exception $e) {
            Log::error('Google login error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', '💥 Terjadi kesalahan. Coba login manual.');
        }
    }
}
