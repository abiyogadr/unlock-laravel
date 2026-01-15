<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Tampilkan form request link reset password.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim link reset password ke email user.
     */
    public function sendResetLinkEmail(Request $request)
    {
        // Validasi input
        $request->validate(['email' => 'required|email']);

        // Kirim link reset password menggunakan Broker Laravel
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Cek status pengiriman
        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', __($status));
        }

        // Jika email tidak ditemukan atau error lain
        return back()->withErrors(['email' => __($status)]);
    }
}
