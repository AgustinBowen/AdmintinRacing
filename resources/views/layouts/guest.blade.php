<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AdmintínRacing - Login</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/admintin.css') }}" rel="stylesheet">
    
    <style>
      body { background: var(--black); }
    </style>
</head>
<body>
    <div class="bg-texture"></div>

    <section class="screen active" id="login">
        <div class="login-wrap">
            <h1 class="login-brand">Admin<span>Tin</span></h1>
            <p class="login-sub">Sistema de Gestión de Campeonatos</p>
            <div class="checker-strip dim login-strip"></div>
            
            @yield('content')
            
            <div class="login-foot">Acceso restringido · Panel de administración</div>
        </div>
    </section>
</body>
</html>