<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admintín - @yield('title', 'Formulario')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="{{ asset('css/admintin.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
      body { background: var(--black); }
    </style>
</head>
<body>
    <div class="bg-texture"></div>

    <section class="screen active fade-in" id="formScreen">
        <div class="form-top">
            <a href="javascript:history.back()" class="back-link">&larr; VOLVER AL PANEL</a>
            <div class="tag">// CARGA MANUAL</div>
        </div>
        <div class="form-body">
            @yield('content')
        </div>
    </section>

    <div id="notify"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        function toast(msg, kind) {
            const n = document.getElementById('notify');
            const t = document.createElement('div');
            t.className = 'toast' + (kind ? ' ' + kind : '');
            t.innerHTML = '<span class="lead">&#9656;</span>' + msg;
            n.appendChild(t);
            setTimeout(() => t.remove(), 3200);
        }

        $(document).ready(function() {
            @if(session('success')) toast("{{ session('success') }}"); @endif
            @if(session('error')) toast("{{ session('error') }}", "error"); @endif
            @if(session('warning')) toast("{{ session('warning') }}", "warn"); @endif
            @if($errors->any()) toast("Error: revisá los campos del formulario", "error"); @endif
        });
    </script>
    @stack('scripts')
</body>
</html>
