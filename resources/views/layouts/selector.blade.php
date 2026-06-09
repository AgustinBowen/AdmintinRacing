<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admintín - @yield('title', 'Selector')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/admintin.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
      body { background: var(--black); }
    </style>
</head>
<body>
    <div class="bg-texture"></div>

    @yield('content')

    <!-- Notificaciones JS -->
    <div id="notify"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
            @if(session('success'))
                toast("{{ session('success') }}");
            @endif
            @if(session('error'))
                toast("{{ session('error') }}", "error");
            @endif
            @if(session('warning'))
                toast("{{ session('warning') }}", "warn");
            @endif
            @if($errors->any())
                toast("Error: revisá los campos del formulario", "error");
            @endif
            
            // Search filter for bands/cards
            $('#catSearch, #yrSearch').on('input', function() {
                var q = $(this).val().toLowerCase();
                var items = $('.band, .yr-card');
                var hasVisible = false;
                
                items.each(function() {
                    var text = $(this).text().toLowerCase();
                    if (text.includes(q)) {
                        $(this).show();
                        hasVisible = true;
                    } else {
                        $(this).hide();
                    }
                });
                
                if (!hasVisible && q.length > 0) {
                    if ($('.empty-hint').length === 0) {
                        $(this).closest('section').append('<div class="empty-hint">Sin coincidencias para "'+q+'"</div>');
                    } else {
                        $('.empty-hint').text('Sin coincidencias para "'+q+'"').show();
                    }
                } else {
                    $('.empty-hint').hide();
                }
            });
        });
    </script>
</body>
</html>
