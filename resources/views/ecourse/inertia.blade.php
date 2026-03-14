<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ecourse - Unlock</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Midtrans Snap -->
    <script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" 
            data-client-key="{{ config('midtrans.client_key') }}"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/ecourse/main-inertia.js'])
    @inertiaHead
</head>
<body class="font-[var(--font-sans)]">
    @inertia
</body>
</html>
