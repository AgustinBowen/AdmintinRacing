<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AdmintínRacing</title>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-theme="light">
    <div class="auth-container fade-in">
        <!-- Brand -->
        <div class="auth-brand">
            <div class="auth-brand-icon">
                <i class="fas fa-flag-checkered"></i>
            </div>
            <h1>AdmintínRacing</h1>
            <p>Panel de Administración</p>
        </div>
        <!-- Auth Card -->
        <div class="auth-card">
            @yield('content')
        </div>
    </div>
</body>
</html>