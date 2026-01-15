<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Unlock Webinar</title>

    <!-- Load Font & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Vite + Tailwind -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-[var(--font-poppins)] bg-gray-50 text-gray-800 min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <!-- Logo -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-6">
        <a href="{{ route('home') }}">
            <img src="{{ asset('assets/images/logo-unlock.png') }}" alt="Unlock Logo" class="mx-auto h-12 hover:opacity-90 transition">
        </a>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-xl shadow-gray-200/50 sm:rounded-2xl sm:px-10 border border-gray-100">

            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900">Buat Akun Baru</h2>
                <p class="mt-2 text-sm text-gray-500">Daftar untuk mulai menggunakan Unlock</p>
            </div>

            <form class="space-y-6" action="{{ route('signup.store') }}" method="POST">
                @csrf

                {{-- Nama Lengkap --}}
                <x-input-field 
                    name="name" 
                    label="Nama Lengkap" 
                    icon="fas fa-user" 
                    :value="old('name')" 
                    placeholder="Nama lengkap"
                    required 
                />

                {{-- Username --}}
                <x-input-field 
                    name="username" 
                    label="Username" 
                    icon="fas fa-at" 
                    :value="old('username')" 
                    placeholder="Username"
                    required 
                />

                {{-- WhatsApp --}}
                <x-input-field 
                    name="whatsapp" 
                    label="No. WhatsApp" 
                    icon="fas fa-phone" 
                    :value="old('whatsapp')" 
                    placeholder="Contoh: 081234567890"
                    required 
                />

                {{-- Email --}}
                <x-input-field 
                    type="email"
                    name="email" 
                    label="Email" 
                    icon="fas fa-envelope" 
                    :value="old('email')" 
                    placeholder="nama@contoh.com"
                    required 
                />

                {{-- Password --}}
                <x-input-field 
                    type="password"
                    name="password" 
                    label="Password" 
                    icon="fas fa-lock" 
                    placeholder="Minimal 8 karakter"
                    required 
                />

                <div>
                    <button type="submit" 
                        class="w-full flex justify-center py-3 px-4 rounded-xl shadow-sm text-sm font-bold text-white bg-primary hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200 hover:shadow-xl cursor-pointer">
                        Daftar Sekarang
                    </button>
                </div>
            </form>

            <p class="mt-8 text-center text-sm text-gray-600">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-medium text-primary hover:text-secondary">
                    Masuk di sini
                </a>
            </p>

        </div>
    </div>
</body>
</html>
