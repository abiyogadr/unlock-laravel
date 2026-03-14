<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-base" content="{{ rtrim(config('app.url'), '/') }}">
    <meta name="user-name" content="{{ Auth::user()->name ?? 'User' }}">
    <meta name="user-email" content="{{ Auth::user()->email ?? 'user@example.com' }}">
    <meta name="user-avatar" content="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('storage/avatar-default.png') }}">
    <meta name="description" content="@yield('meta_description', 'Unlock Indonesia: Platform Webinar Bersertifikat.')">
    <title>@yield('title', 'E-Course - Unlock Indonesia')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans+Flex:opsz,wght@8..144,100..1000&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/ecourse/main.js'])
</head>
<body class="bg-gray-50 font-sans" style="font-family: 'Google Sans Flex', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;">
    <div id="ecourse-app"></div>
</body>
</html>
