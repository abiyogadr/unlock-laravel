<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Password Baru - Unlock Webinar</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-[var(--font-poppins)] bg-gray-50 text-gray-800 min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-6">
        <a href="{{ route('home') }}">
            <img src="{{ asset('assets/images/logo-unlock.png') }}" alt="Unlock Logo" class="mx-auto h-12">
        </a>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-xl shadow-gray-200/50 sm:rounded-2xl sm:px-10 border border-gray-100">
            
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900">Password Baru</h2>
                <p class="mt-2 text-sm text-gray-500">Silakan buat password baru untuk akun Anda.</p>
            </div>

            <!-- Error Message -->
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                    <ul class="list-disc list-inside text-sm text-red-700 font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="space-y-6" action="{{ route('password.update') }}" method="POST">
                @csrf
                
                <!-- Token wajib ada (hidden) -->
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email (Readonly agar user yakin ini akun yang benar) -->
                <x-input-field 
                    type="email"
                    name="email" 
                    label="Email" 
                    :value="$email ?? old('email')" 
                    inputClass="bg-gray-100 text-gray-500 rounded-lg py-3 cursor-not-allowed"
                    readonly 
                    required 
                />

                <!-- Password Baru -->
                <x-input-field 
                    type="password"
                    name="password" 
                    label="Password Baru" 
                    icon="fas fa-lock" 
                    placeholder="••••••••"
                    inputClass="rounded-lg py-3"
                    autofocus
                    required 
                />

                <!-- Konfirmasi Password -->
                <x-input-field 
                    type="password"
                    name="password_confirmation" 
                    label="Konfirmasi Password" 
                    icon="fas fa-lock" 
                    placeholder="••••••••"
                    inputClass="rounded-lg py-3"
                    required 
                />

                <div>
                    <button type="submit" 
                        class="w-full flex justify-center py-3 px-4 rounded-xl shadow-sm text-sm font-bold text-white bg-primary hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200 hover:shadow-xl cursor-pointer">
                        Ubah Password
                    </button>
                </div>
            </form>

        </div>
    </div>
</body>
</html>
