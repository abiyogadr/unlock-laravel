<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Unlock Webinar</title>
    
    <!-- Load Font & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Vite + Tailwind -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-[var(--font-poppins)] bg-gray-50 text-gray-800 min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <!-- Logo di atas Box Login -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-6">
        <a href="{{ route('home') }}">
            <img src="{{ asset('assets/images/logo-unlock.png') }}" alt="Unlock Logo" class="mx-auto h-12 hover:opacity-90 transition">
        </a>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-xl shadow-gray-200/50 sm:rounded-2xl sm:px-10 border border-gray-100">
            
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900">Selamat Datang Kembali</h2>
                <p class="mt-2 text-sm text-gray-500">Masuk ke akun Unlock Anda</p>
            </div>

            <!-- Success Message -->
             @if (session('success'))
                <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Error Message -->
            @if (session('error'))
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Form Utama -->
            <form class="space-y-6" action="{{ route('login.process') }}" method="POST">
                @csrf
                
                {{-- Input Login (Email/Username/HP) --}}
                <x-input-field 
                    name="login" 
                    label="Email / Username / No. HP" 
                    icon="fas fa-user" 
                    :value="old('login')" 
                    placeholder="Masukan akun anda"
                    autocomplete="username"
                    required 
                />

                {{-- Input Password --}}
                <x-input-field 
                    type="password"
                    name="password" 
                    label="Password" 
                    icon="fas fa-lock" 
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required 
                />

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" 
                            class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded cursor-pointer">
                        <label for="remember" class="ml-2 block text-sm text-gray-900 cursor-pointer">Ingat saya</label>
                    </div>

                    <div class="text-sm">
                        <a href="{{ route('password.request') }}" class="font-medium text-primary hover:text-secondary">Lupa password?</a>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="w-full flex justify-center py-3 px-4 rounded-xl shadow-sm text-sm font-bold text-white bg-primary hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200 hover:shadow-xl cursor-pointer">
                        Masuk Sekarang
                    </button>
                </div>
            </form>

            <!-- DIVIDER -->
            <div class="mt-8">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Atau masuk dengan</span>
                    </div>
                </div>

                <!-- TOMBOL GOOGLE -->
                <div class="mt-6">
                    <a href="{{ url('/auth/google') }}" 
                       class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200">
                        <img class="h-5 w-5 mr-3" src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google Logo">
                        Masuk dengan Google
                    </a>
                </div>
            </div>
            
            <!-- Link Register -->
            <p class="mt-8 text-center text-sm text-gray-600">
                Belum punya akun? 
                <a href="{{ route('signup.create', ['redirect' => request()->query('redirect')]) }}" class="font-medium text-primary hover:text-secondary">
                    Daftar di sini
                </a>
            </p>

        </div>
    </div>
</body>
</html>
