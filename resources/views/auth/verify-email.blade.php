<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - Unlock Webinar</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-[var(--font-poppins)] bg-gray-50 text-gray-800 min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-6">
        <a href="{{ route('home') }}">
            <img src="{{ asset('assets/images/logo-unlock.png') }}" alt="Unlock Logo" class="mx-auto h-12 hover:opacity-90 transition">
        </a>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-xl shadow-gray-200/50 sm:rounded-2xl sm:px-10 border border-gray-100">
            
            <div class="mb-6 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-50 mb-4">
                    <i class="fas fa-envelope-open-text text-primary text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Verifikasi Email Anda</h2>
                <p class="mt-2 text-sm text-gray-500">
                    Kami telah mengirimkan link aktivasi ke email Anda. Silakan klik link tersebut untuk mengaktifkan akun.
                </p>
            </div>

            @if (session('message'))
                <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
                    <p class="text-sm text-green-700 font-medium">Link verifikasi baru telah dikirim!</p>
                </div>
            @endif

            <div class="space-y-4">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" id="resendButton"
                        class="w-full flex justify-center py-3 px-4 rounded-xl shadow-sm text-sm font-bold text-white bg-primary hover:bg-secondary disabled:bg-gray-400 disabled:cursor-not-allowed transition-all duration-200">
                        Kirim Ulang Email (<span id="timer">120</span>s)
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                    <a href="{{ route('home') }}" 
                    class="inline-flex items-center text-sm font-semibold text-gray-500 hover:text-primary transition-colors duration-200 group">
                        <div class="w-8 h-8 rounded-full bg-gray-100 group-hover:bg-blue-50 flex items-center justify-center mr-2 transition-colors">
                            <i class="fas fa-home text-xs"></i>
                        </div>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let timeLeft = 120;
            const timerElement = document.getElementById('timer');
            const resendButton = document.getElementById('resendButton');

            // Set button to disabled initially
            resendButton.disabled = true;

            const countdown = setInterval(function() {
                timeLeft--;
                timerElement.innerText = timeLeft;

                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    resendButton.disabled = false;
                    resendButton.innerText = "Kirim Ulang Sekarang";
                }
            }, 1000);
        });
    </script>
</body>
</html>
