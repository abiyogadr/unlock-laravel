<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Unlock Webinar</title>
    
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
            <!-- Pastikan path logo sesuai -->
            <img src="{{ asset('assets/images/logo-unlock.png') }}" alt="Unlock Logo" class="mx-auto h-12 hover:opacity-90 transition">
        </a>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-xl shadow-gray-200/50 sm:rounded-2xl sm:px-10 border border-gray-100">
            
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900">Reset Password</h2>
                <p class="mt-2 text-sm text-gray-500">Masukkan email yang terdaftar untuk menerima instruksi reset password.</p>
            </div>

            <!-- Success Message -->
             @if (session('success'))
                <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Error Message (General / Validation) -->
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <ul class="list-disc list-inside text-sm text-red-700 font-medium">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Form Utama -->
            <form class="space-y-6" action="{{ route('password.email') }}" method="POST">
                @csrf

                {{-- Email --}}
                <x-input-field 
                    name="email"
                    id="email"
                    type="email" 
                    label="Alamat Email" 
                    icon="fas fa-envelope" 
                    :value="old('email')" 
                    placeholder="Masukan email anda"
                    autocomplete="email"
                    required 
                />

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 rounded-xl shadow-sm text-sm font-bold text-white bg-primary hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200 hover:shadow-xl cursor-pointer">
                        Kirim Link Reset
                    </button>
                </div>
            </form>

            <!-- Link Kembali Login -->
            <p class="mt-8 text-center text-sm text-gray-600">
                Sudah ingat password? 
                <a href="{{ route('login') }}" class="font-medium text-primary hover:text-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Login
                </a>
            </p>

        </div>
    </div>
</body>
</html>
