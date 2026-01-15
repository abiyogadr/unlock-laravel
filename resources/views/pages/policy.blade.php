@extends('layouts.app')

@section('title', 'Unlock - Kebijakan Privasi')

@section('content')

    {{-- Header Section --}}
    <section class="w-full bg-gradient-to-br from-primary to-purple-800 text-white py-16">
        <div class="container mx-auto px-4 text-center max-w-7xl">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">Kebijakan Privasi</h1>
            <p class="text-lg opacity-90">Terakhir diperbarui: {{ date('d F Y') }}</p>
        </div>
    </section>

    {{-- Content Section --}}
    <section class="w-full py-16 bg-white">
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="prose prose-blue max-w-none text-gray-600 space-y-8">
                
                {{-- Komitmen --}}
                <div class="bg-blue-50 p-6 rounded-2xl border-l-4 border-primary">
                    <p class="font-medium text-blue-900 m-0 text-lg">
                        Unlock Indonesia berkomitmen untuk menjaga dan melindungi data pribadi pengguna. Seluruh data yang diberikan hanya digunakan untuk keperluan pendaftaran event, distribusi informasi, dan pengiriman sertifikat.
                    </p>
                </div>

                <div class="space-y-6">
                    {{-- 1. Pengumpulan Data --}}
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                            <span class="bg-primary text-white w-8 h-8 rounded-lg flex items-center justify-center mr-3 text-sm">1</span>
                            Data yang Kami Kumpulkan
                        </h2>
                        <p>Kami mengumpulkan informasi yang Anda berikan secara langsung saat pendaftaran:</p>
                        <ul class="list-disc ml-6 space-y-2">
                            <li><strong>Identitas Pribadi:</strong> Nama lengkap, alamat email, dan nomor WhatsApp.</li>
                            <li><strong>Log Transaksi:</strong> Informasi terkait pembayaran melalui gateway Midtrans.</li>
                            <li><strong>Data Google Auth:</strong> Jika Anda masuk menggunakan Google, kami menerima nama, email, dan foto profil Anda dari penyedia layanan tersebut.</li>
                        </ul>
                    </div>

                    {{-- 2. Penggunaan Data --}}
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                            <span class="bg-primary text-white w-8 h-8 rounded-lg flex items-center justify-center mr-3 text-sm">2</span>
                            Tujuan Penggunaan Data
                        </h2>
                        <p>Unlock Indonesia menggunakan data tersebut untuk:</p>
                        <ul class="list-disc ml-6 space-y-2">
                            <li>Memproses pendaftaran event dan manajemen kehadiran.</li>
                            <li>Pengiriman sertifikat resmi dan materi pembelajaran.</li>
                            <li>Notifikasi jadwal webinar melalui WhatsApp dan Email.</li>
                            <li>Analisis internal untuk meningkatkan kualitas layanan aplikasi berbasis Laravel kami.</li>
                        </ul>
                    </div>

                    {{-- 3. Layanan Pihak Ketiga --}}
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                            <span class="bg-primary text-white w-8 h-8 rounded-lg flex items-center justify-center mr-3 text-sm">3</span>
                            Pihak Ketiga dan Keamanan
                        </h2>
                        <p>Kami bekerja sama dengan penyedia layanan pihak ketiga yang terpercaya:</p>
                        <ul class="list-disc ml-6 space-y-2">
                            <li><strong>Midtrans:</strong> Semua data pembayaran diproses melalui koneksi terenkripsi Midtrans. Unlock tidak menyimpan data kartu kredit atau kredensial akun bank Anda.</li>
                            <li><strong>Google:</strong> Penggunaan data dari API Google mematuhi Kebijakan Data Pengguna Layanan Google API.</li>
                            <li><strong>Layanan Kolaborasi:</strong> Data mungkin dibagikan kepada institusi partner resmi yang terlibat dalam program tertentu atas persetujuan Anda.</li>
                        </ul>
                    </div>

                </div>

                {{-- Contact Support --}}
                <div class="mt-12 p-8 border border-gray-100 rounded-3xl bg-gray-50 text-center">
                    <h3 class="font-bold text-gray-900 mb-2">Punya pertanyaan tentang Privasi?</h3>
                    <p class="text-sm mb-6">Silakan hubungi petugas perlindungan data kami.</p>
                    <a href="https://wa.me/6285176767623" target="_blank" 
                        class="bg-primary text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-secondary transition-all shadow-md inline-flex items-center">
                    <i class="fab fa-whatsapp mr-2"></i> Hubungi Support Unlock
                </a>

            </div>
        </div>
    </section>

@endsection
