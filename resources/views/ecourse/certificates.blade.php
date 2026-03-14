@extends('ecourse.layouts.app')

@section('title', 'E-Sertifikat - Unlock E-Course')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-7xl">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">E-Sertifikat</h1>
            <p class="text-gray-600">Kumpulan sertifikat yang telah kamu peroleh</p>
        </div>

        <!-- Certificates Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($certificates as $certificate)
                <article class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition">
                    <!-- Certificate Preview -->
                    <div class="relative h-64 bg-gradient-to-br from-primary to-purple-800 p-8 flex flex-col justify-center items-center text-white">
                        <div class="text-center">
                            <i class="fas fa-certificate text-6xl mb-4 text-yellow-300"></i>
                            <h3 class="text-xl font-bold mb-2">{{ $certificate->course_title }}</h3>
                            <p class="text-sm opacity-90">{{ $certificate->user_name }}</p>
                            <p class="text-xs opacity-75 mt-2">{{ $certificate->completed_at->format('d M Y') }}</p>
                        </div>
                        
                        <!-- Seal/Logo -->
                        <div class="absolute bottom-4 right-4 w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-white text-2xl"></i>
                        </div>
                    </div>

                    <!-- Certificate Info -->
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm text-gray-500">Nomor Sertifikat</p>
                                <p class="font-mono text-sm font-semibold text-gray-800">{{ $certificate->certificate_number }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Skor</p>
                                <p class="text-lg font-bold text-primary">{{ $certificate->score }}%</p>
                            </div>
                        </div>

                        <!-- Download & Share -->
                        <div class="flex gap-3">
                            <a href="{{ route('certificate.view', $certificate->certificate_number) }}" class="flex-1 text-center bg-primary text-white py-2.5 rounded-xl font-semibold hover:bg-purple-900 transition">
                                <i class="fas fa-eye mr-2"></i> Lihat Sertifikat
                            </a>
                            <button class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition" onclick="shareCertificate('{{ $certificate->id }}')">
                                <i class="fas fa-share-alt text-gray-600"></i>
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>

    <script>
        function shareCertificate(certificateId) {
            if (navigator.share) {
                navigator.share({
                    title: 'Sertifikat E-Course Unlock Indonesia',
                    text: 'Lihat sertifikat yang saya peroleh dari Unlock Indonesia!',
                    url: '{{ url("/certificate") }}/' + certificateId
                });
            } else {
                // Fallback untuk browser yang tidak support Web Share API
                const url = '{{ url("/certificate") }}/' + certificateId;
                navigator.clipboard.writeText(url).then(() => {
                    alert('Link sertifikat telah disalin ke clipboard!');
                });
            }
        }
    </script>
@endsection
