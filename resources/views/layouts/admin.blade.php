<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admintín - @yield('title', 'Panel Admin')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    
    <!-- Select2 CSS (sin Bootstrap theme) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/admintin.css') }}" rel="stylesheet">
    
    <!-- Notificaciones y FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div class="bg-texture"></div>

    <section class="screen active {{ session('animate_entry') ? 'animate-entry' : '' }}" id="panel">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sb-brand">
                <a href="{{ route('admin.dashboard') }}" class="logo" style="text-decoration:none;">Admin<span>Tin</span></a>
                <div class="tagline">Gestión de campeonatos</div>
            </div>
            
            <nav class="sb-nav" id="navList">
                <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="ic"><x-heroicon-o-home /></span>
                    <span>Inicio</span>
                </a>
                @if(session()->has('campeonato_id'))
                <a href="{{ route('admin.circuitos.index') }}" class="nav-item {{ request()->routeIs('admin.circuitos.*') ? 'active' : '' }}">
                    <span class="ic"><x-heroicon-o-map /></span>
                    <span>Circuitos</span>
                </a>
                <a href="{{ route('admin.pilotos.index') }}" class="nav-item {{ request()->routeIs('admin.pilotos.*') ? 'active' : '' }}">
                    <span class="ic"><x-heroicon-o-user /></span>
                    <span>Pilotos</span>
                </a>
                <a href="{{ route('admin.fechas.index') }}" class="nav-item {{ request()->routeIs('admin.fechas.*') || request()->routeIs('admin.sesiones.*') || request()->routeIs('admin.horarios.*') || request()->routeIs('admin.resultados.*') ? 'active' : '' }}">
                    <span class="ic"><x-heroicon-o-calendar-date-range /></span>
                    <span>Calendario</span>
                </a>
                
                <a href="{{ route('admin.campeonatos.show', session('campeonato_id')) }}" class="nav-item {{ request()->routeIs('admin.campeonatos.show') || request()->routeIs('admin.campeonatos.edit') ? 'active' : '' }}">
                    <span class="ic"><x-heroicon-o-table-cells /></span>
                    <span>Campeonato</span>
                </a>
                @endif
            </nav>
            
            <div class="sb-foot">
                <span>Sesión //</span>
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="exit">Salir &#8634;</button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main">
            <div class="topbar">
                <div class="breadcrumb" style="white-space: nowrap;">
                    <span class="seg cur">@yield('title')</span>
                </div>
                <div class="telemetry" style="display:flex; align-items:center; gap: 14px; white-space: nowrap;">
                    @if(session()->has('campeonato_id'))
                        <a href="{{ route('admin.categorias.index') }}" class="btn ghost sm" style="white-space: nowrap;">Cambiar Categoría</a>
                        <a href="{{ route('admin.categorias.show', session('categoria_id')) }}" class="btn ghost sm" style="white-space: nowrap;">Cambiar Temporada</a>
                        <div style="width: 1px; height: 24px; background: var(--line); margin: 0 4px;"></div>
                    @endif
                    <span>Usuario <b>{{ auth()->user()->name ?? 'Admin' }}</b></span>
                </div>
            </div>
            
            <div class="content">
                @yield('content')
            </div>
        </div>
    </section>

    <!-- Dynamic Form Container -->
    <div id="dynamicFormContainer"></div>

    <!-- Notificaciones JS -->
    <div id="notify"></div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 JS -->
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
        });

        // Dynamic Form Logic (PJAX)
        $(document).on('click', 'a[href*="/create"], a[href*="/edit"]', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');
            
            // Use PJAX-like fetch
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.text())
                .then(html => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');
                    let formScreen = doc.querySelector('#formScreen');
                    
                    if(formScreen) {
                        $('#panel').removeClass('active');
                        
                        let backBtn = formScreen.querySelector('.back-link');
                        if(backBtn) {
                            backBtn.removeAttribute('href');
                            backBtn.onclick = function(ev) {
                                ev.preventDefault();
                                $('#dynamicFormContainer').empty();
                                $('#panel').addClass('active');
                            };
                        }
                        
                        let cancelBtn = formScreen.querySelector('.btn.ghost');
                        if(cancelBtn && cancelBtn.tagName === 'A') {
                            cancelBtn.removeAttribute('href');
                            cancelBtn.onclick = function(ev) {
                                ev.preventDefault();
                                $('#dynamicFormContainer').empty();
                                $('#panel').addClass('active');
                            };
                        }

                        $('#dynamicFormContainer').empty().append(formScreen);
                        
                        // Execute scripts in the fetched form
                        let scripts = formScreen.querySelectorAll('script');
                        scripts.forEach(s => {
                            if (!s.src) {
                                eval(s.innerText);
                            }
                        });
                        
                        window.history.pushState({ dynamicForm: true }, '', url);
                    } else {
                        window.location.href = url;
                    }
                });
        });

        // Intercept Form Submit
        $(document).on('submit', '#dynamicFormContainer form', function(e) {
            e.preventDefault();
            let form = $(this);
            let url = form.attr('action');
            let method = form.attr('method') || 'POST';
            
            // Check if there is a _method override
            let methodOverride = form.find('input[name="_method"]').val();
            
            let formData = new FormData(this);
            
            let btn = form.find('button[type="submit"]');
            let originalText = btn.html();
            btn.prop('disabled', true).html('GUARDANDO...');
            
            fetch(url, {
                method: method, // Always POST for Laravel forms, _method inside FormData handles PUT/PATCH
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            }).then(async res => {
                if(res.redirected) {
                     // Controller redirected to index, res already contains the fetched HTML
                     let nextHtml = await res.text();
                     let parser = new DOMParser();
                     let doc = parser.parseFromString(nextHtml, 'text/html');
                     
                     $('.content').html(doc.querySelector('.content').innerHTML);
                     $('#dynamicFormContainer').empty();
                     $('#panel').addClass('active');
                     window.history.pushState({}, '', res.url);
                     
                     // Run toast from redirected page
                     let docScripts = doc.querySelectorAll('script');
                     docScripts.forEach(s => {
                        if(!s.src && s.innerText.includes('toast(')) {
                            eval(s.innerText);
                        }
                     });
                } else if (res.status === 422) {
                     let data = await res.json();
                     form.find('.text-danger').remove();
                     form.find('input, select, textarea').css('border-color', '');
                     for(let field in data.errors) {
                         // Convert dot notation (e.g. meta.x) to array notation (meta[x]) if needed
                         let inputName = field.includes('.') ? field.split('.').reduce((a,c,i) => i===0 ? c : a+'['+c+']', '') : field;
                         let input = form.find('[name="'+inputName+'"]');
                         if(input.length === 0) input = form.find('[name="'+field+'"]');
                         
                         input.css('border-color', 'var(--racing)');
                         input.after('<div class="text-danger" style="font-size:12px; margin-top:6px;">'+data.errors[field][0]+'</div>');
                     }
                     toast("Error: revisá los campos del formulario", "error");
                     btn.prop('disabled', false).html(originalText);
                } else {
                     toast("Error en el servidor o redirigido", "error");
                     btn.prop('disabled', false).html(originalText);
                }
            }).catch(err => {
                toast("Error en la conexión", "error");
                btn.prop('disabled', false).html(originalText);
            });
        });

        window.addEventListener('popstate', function(e) {
            if ($('#dynamicFormContainer').children().length > 0) {
                $('#dynamicFormContainer').empty();
                $('#panel').addClass('active');
            } else {
                window.location.reload();
            }
        });

        // Global Custom Modal Handler
        document.addEventListener('click', function(e) {
            // Open modals
            const toggleBtn = e.target.closest('[data-bs-toggle="modal"]');
            if (toggleBtn) {
                const targetSelector = toggleBtn.getAttribute('data-bs-target');
                if (targetSelector) {
                    const targetModal = document.querySelector(targetSelector);
                    if (targetModal && targetModal.classList.contains('custom-modal')) {
                        e.preventDefault();
                        targetModal.classList.add('show');
                    }
                }
            }

            // Close modals with dismiss button
            const dismissBtn = e.target.closest('[data-dismiss="modal"]');
            if (dismissBtn) {
                const modal = dismissBtn.closest('.custom-modal');
                if (modal) {
                    e.preventDefault();
                    modal.classList.remove('show');
                }
            }
        });

        // Close modals on clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('custom-modal') && e.target.classList.contains('show')) {
                e.target.classList.remove('show');
            }
        });

        // Drag to scroll for tables
        document.addEventListener('mousedown', function(e) {
            const wrap = e.target.closest('.tbl-wrap');
            if (!wrap) return;
            
            let isDown = true;
            let startX = e.pageX - wrap.offsetLeft;
            let scrollLeft = wrap.scrollLeft;
            
            const onMouseMove = (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - wrap.offsetLeft;
                const walk = (x - startX) * 1.5; 
                wrap.scrollLeft = scrollLeft - walk;
            };
            
            const onMouseUp = () => {
                isDown = false;
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
            };
            
            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });
    </script>
    
    @stack('scripts')
</body>
</html>