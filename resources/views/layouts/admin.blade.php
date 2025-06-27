<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Admin') - {{ config('app.name', '1100 Admin') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Layout */
            --sidebar-width: 280px;
            --header-height: 64px;

            /* Colors - Light Mode */
            --primary: 0 0% 0%;
            /* Black */
            --secondary: 0 0% 100%;
            /* White */
            --accent: 0 84% 60%;
            /* Red */
            --muted: 210 40% 98%;
            /* Light gray */
            --muted-foreground: 215 16% 47%;
            --border: 214 32% 91%;
            --input: 214 32% 91%;
            --ring: 0 84% 60%;
            --background: 0 0% 100%;
            --foreground: 222 84% 5%;
            --card: 0 0% 100%;
            --card-foreground: 222 84% 5%;
            --popover: 0 0% 100%;
            --popover-foreground: 222 84% 5%;
            --destructive: 0 84% 60%;
            --destructive-foreground: 210 40% 98%;
            --success: 142 76% 36%;
            --success-foreground: 355 100% 97%;
            --warning: 38 92% 50%;
            --warning-foreground: 48 96% 89%;

            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);

            /* Border Radius */
            --radius: 0.5rem;
        }

        [data-theme="dark"] {
            --primary: 0 0% 0%;
            /* Black - mantener negro como primario */
            --secondary: 0 0% 100%;
            /* White - blanco para contraste */
            --accent: 0 84% 60%;
            /* Red - rojo se mantiene igual */
            --muted: 217 33% 17%;
            /* Dark gray */
            --muted-foreground: 215 20% 65%;
            --border: 217 33% 17%;
            --input: 217 33% 17%;
            --ring: 0 84% 60%;
            --background: 224 71% 4%;
            /* Fondo oscuro */
            --foreground: 213 31% 91%;
            /* Texto claro */
            --card: 224 71% 4%;
            --card-foreground: 213 31% 91%;
            --popover: 224 71% 4%;
            --popover-foreground: 213 31% 91%;
            --destructive: 0 84% 60%;
            --destructive-foreground: 210 40% 98%;
            --success: 142 76% 36%;
            --success-foreground: 355 100% 97%;
            --warning: 38 92% 50%;
            --warning-foreground: 48 96% 89%;
        }

        * {
            border-color: hsl(var(--border));
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: hsl(var(--background));
            color: hsl(var(--foreground));
            font-size: 14px;
            line-height: 1.5;
            font-feature-settings: "cv02", "cv03", "cv04", "cv11";
        }

        /* Base Components */
        .btn-modern {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary-modern {
            background-color: hsl(var(--primary));
            color: hsl(var(--secondary));
            padding: 0.5rem 1rem;
        }

        .btn-primary-modern:hover {
            background-color: hsl(var(--primary) / 0.9);
            color: hsl(var(--secondary));
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary-modern {
            background-color: hsl(var(--secondary));
            color: hsl(var(--primary));
            border: 1px solid hsl(var(--border));
            padding: 0.5rem 1rem;
        }

        .btn-secondary-modern:hover {
            background-color: hsl(var(--muted));
            color: hsl(var(--primary));
        }

        .btn-destructive-modern {
            background-color: hsl(var(--accent));
            color: hsl(var(--secondary));
            padding: 0.5rem 1rem;
        }

        .btn-destructive-modern:hover {
            background-color: hsl(var(--accent) / 0.9);
            color: hsl(var(--secondary));
        }

        .card-modern {
            background-color: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .card-header-modern {
            background-color: hsl(var(--muted) / 0.5);
            border-bottom: 1px solid hsl(var(--border));
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: hsl(var(--card-foreground));
        }

        .card-body-modern {
            padding: 1.5rem;
        }

        .input-modern {
            display: flex;
            width: 100%;
            border-radius: var(--radius);
            border: 1px solid hsl(var(--border));
            background-color: hsl(var(--background));
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            color: hsl(var(--foreground));
        }

        .input-modern:focus {
            outline: none;
            border-color: hsl(var(--ring));
            box-shadow: 0 0 0 2px hsl(var(--ring) / 0.2);
        }

        .input-modern::placeholder {
            color: hsl(var(--muted-foreground));
        }

        .badge-modern {
            display: inline-flex;
            align-items: center;
            border-radius: calc(var(--radius) - 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .badge-primary {
            background-color: hsl(var(--primary));
            color: hsl(var(--secondary));
        }

        .badge-secondary {
            background-color: hsl(var(--muted));
            color: hsl(var(--muted-foreground));
        }

        .badge-destructive {
            background-color: hsl(var(--accent));
            color: hsl(var(--secondary));
        }

        .badge-success {
            background-color: hsl(var(--success));
            color: hsl(var(--success-foreground));
        }

        .badge-warning {
            background-color: hsl(var(--warning));
            color: hsl(var(--warning-foreground));
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background-color: hsl(var(--card));
            border-right: 1px solid hsl(var(--border));
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid hsl(var(--border));
            text-align: center;
        }

        .sidebar-brand h4 {
            margin: 0;
            font-weight: 700;
            font-size: 1.25rem;
            color: hsl(var(--foreground));
            letter-spacing: -0.025em;
        }

        .sidebar-brand .brand-icon {
            color: hsl(var(--accent));
            margin-right: 0.5rem;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin-bottom: 0.25rem;
            padding: 0 1rem;
        }

        .nav-link {
            color: hsl(var(--muted-foreground));
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            text-decoration: none;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .nav-link:hover {
            background-color: hsl(var(--muted));
            color: hsl(var(--foreground));
        }

        .nav-link.active {
            background-color: hsl(var(--accent));
            color: hsl(var(--secondary));
            font-weight: 600;
        }

        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
            text-align: center;
            font-size: 1rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-content.expanded {
            margin-left: 70px;
        }

        .top-navbar {
            background-color: hsl(var(--background));
            height: var(--header-height);
            border-bottom: 1px solid hsl(var(--border));
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .content-wrapper {
            padding: 2rem;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: hsl(var(--muted-foreground));
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: var(--radius);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-toggle:hover {
            background-color: hsl(var(--muted));
            color: hsl(var(--foreground));
        }

        .theme-toggle {
            background: none;
            border: none;
            color: hsl(var(--muted-foreground));
            font-size: 1.125rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: var(--radius);
            margin-right: 1rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .theme-toggle:hover {
            background-color: hsl(var(--muted));
            color: hsl(var(--foreground));
        }

        /* Alerts */
        .alert-modern {
            border: none;
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .alert-success-modern {
            background-color: hsl(var(--success) / 0.1);
            color: hsl(var(--success));
            border-left: 4px solid hsl(var(--success));
        }

        .alert-danger-modern {
            background-color: hsl(var(--accent) / 0.1);
            color: hsl(var(--accent));
            border-left: 4px solid hsl(var(--accent));
        }

        .alert-warning-modern {
            background-color: hsl(var(--warning) / 0.1);
            color: hsl(var(--warning));
            border-left: 4px solid hsl(var(--warning));
        }

        /* Tables */
        .table-modern {
            width: 100%;
            border-collapse: collapse;
        }

        .table-modern th {
            background-color: hsl(var(--muted) / 0.5);
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: hsl(var(--muted-foreground));
            border-bottom: 1px solid hsl(var(--border));
        }

        .table-modern td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid hsl(var(--border));
            color: hsl(var(--foreground));
        }

        .table-modern tbody tr:hover {
            background-color: hsl(var(--muted) / 0.5);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .content-wrapper {
                padding: 1rem;
            }

            .top-navbar {
                padding: 0 1rem;
            }
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Custom scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: hsl(var(--border));
            border-radius: 2px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: hsl(var(--muted-foreground));
        }
    </style>

    @stack('styles')
</head>

<body data-theme="light">
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h4>
                <i class="fas fa-flag-checkered brand-icon"></i>
                <span class="brand-text">1100 Admin</span>
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