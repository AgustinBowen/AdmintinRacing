<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admintín - @yield('title', 'Panel Admin')</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body data-theme="light">
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h4>
                <div class="brand-icon">
                    <i class="fas fa-flag-checkered"></i>
                </div>
                <span class="brand-text">AdmintínRacing</span>
            </h4>
        </div>

        <ul class="sidebar-nav list-unstyled">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Generador de Fechas Automatico</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.campeonatos.index') }}" class="nav-link {{ request()->routeIs('admin.campeonatos.*') ? 'active' : '' }}">
                    <i class="fas fa-trophy"></i>
                    <span>Campeonatos</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.circuitos.index') }}" class="nav-link {{ request()->routeIs('admin.circuitos.*') ? 'active' : '' }}">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Circuitos</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.pilotos.index') }}" class="nav-link {{ request()->routeIs('admin.pilotos.*') ? 'active' : '' }}">
                    <i class="fas fa-user-circle"></i>
                    <span>Pilotos</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.fechas.index') }}" class="nav-link {{ request()->routeIs('admin.fechas.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check"></i>
                    <span>Fechas</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.sesiones.index') }}" class="nav-link {{ request()->routeIs('admin.sesiones.*') ? 'active' : '' }}">
                    <i class="fas fa-clock"></i>
                    <span>Sesiones</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.resultados.index') }}" class="nav-link {{ request()->routeIs('admin.resultados.*') ? 'active' : '' }}">
                    <i class="fas fa-clock"></i>
                    <span>Resultados</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.horarios.index') }}" class="nav-link {{ request()->routeIs('admin.horarios.*') ? 'active' : '' }}">
                    <i class="fas fa-clock"></i>
                    <span>Horarios</span>
                </a>
            </li>
        </ul>
    </nav>
    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="sidebar-toggle" id="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <div class="d-flex align-items-center">
                <!-- Theme Toggle -->
                <button class="theme-toggle" id="theme-toggle" title="Cambiar tema">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn-secondary-modern dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name ?? 'Admin' }}&background=000000&color=ffffff&size=32"
                            class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                        <span>{{ auth()->user()->name ?? 'Admin' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="background-color: hsl(var(--popover)); border: 1px solid hsl(var(--border)); box-shadow: var(--shadow-lg);">
                        <li>
                            <a class="dropdown-item" href="#" style="color: hsl(var(--popover-foreground));">
                                <i class="fas fa-user me-2"></i>Perfil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" style="color: hsl(var(--popover-foreground));">
                                <i class="fas fa-cog me-2"></i>Configuración
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider" style="border-color: hsl(var(--border));">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item" style="color: hsl(var(--accent));">
                                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- Content Wrapper -->
        <div class="content-wrapper fade-in">
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="alert-modern alert-success-modern" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="alert-modern alert-danger-modern" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ session('error') }}
            </div>
            @endif

            @if(session('warning'))
            <div class="alert-modern alert-warning-modern" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('warning') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert-modern alert-danger-modern" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>
                    <strong>¡Hay errores en el formulario!</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
            <!-- Main Content -->
            @yield('content')
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Scripts -->
    <script>
        // Theme Management
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const body = document.body;

        // Load saved theme or default to light
        const savedTheme = localStorage.getItem('theme') || 'light';
        body.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);

        themeToggle.addEventListener('click', function() {
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';

            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });

        function updateThemeIcon(theme) {
            themeIcon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }

        // Sidebar Toggle
        document.getElementById('sidebar-toggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');

            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            }
        });

        // Close mobile sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                const sidebar = document.getElementById('sidebar');
                const sidebarToggle = document.getElementById('sidebar-toggle');

                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-modern');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
    @stack('scripts')
</body>

</html>