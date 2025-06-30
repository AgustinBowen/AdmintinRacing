<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', '1100Admin') }}</title>

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
            --primary: 0 0% 3.9%;
            /* Black */
            --secondary: 0 0% 100%;
            /* White */
            --accent: 0 72.2% 50.6%;
            /* Red */
            --muted: 210 40% 98%;
            /* Light gray */
            --badge-background: 82.7 78% 55.5%;
            --badge-background-hover: 82 84.5% 67.1%;
            --muted-foreground: 0 0% 3.9%;
            --placeholder: 0 0% 45.3%;
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
            -webkit-font-smoothing: antialiased;
            margin: 0;
            padding: 0;
        }

        /* Auth Layout Styles */
        .auth-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, hsl(var(--muted)) 0%, hsl(var(--background)) 100%);
            position: relative;
        }

        .auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(circle at 25% 25%, hsl(var(--accent) / 0.1) 0%, transparent 50%),
                              radial-gradient(circle at 75% 75%, hsl(var(--accent) / 0.05) 0%, transparent 50%);
            pointer-events: none;
        }

        .auth-brand {
            margin-bottom: 2rem;
            text-align: center;
            z-index: 1;
        }

        .auth-brand-icon {
            width: 4rem;
            height: 4rem;
            background-color: hsl(var(--primary));
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: var(--shadow-lg);
        }

        .auth-brand-icon i {
            font-size: 2rem;
            color: hsl(var(--secondary));
        }

        .auth-brand h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: hsl(var(--foreground));
            margin: 0 0 0.5rem;
            letter-spacing: -0.025em;
        }

        .auth-brand p {
            color: hsl(var(--muted-foreground));
            font-size: 0.875rem;
            margin: 0;
        }

        .auth-card {
            width: 100%;
            max-width: 28rem;
            background-color: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: calc(var(--radius) + 4px);
            box-shadow: var(--shadow-xl);
            padding: 2rem;
            z-index: 1;
            position: relative;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: hsl(var(--card-foreground));
            margin: 0 0 0.5rem;
            letter-spacing: -0.025em;
        }

        .auth-header p {
            color: hsl(var(--muted-foreground));
            font-size: 0.875rem;
            margin: 0;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: hsl(var(--card-foreground));
            margin-bottom: 0.5rem;
        }

        .form-input {
            display: flex;
            width: 100%;
            border-radius: var(--radius);
            border: 1px solid hsl(var(--border));
            background-color: hsl(var(--background));
            padding: 0.75rem;
            font-size: 0.875rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            color: hsl(var(--foreground));
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: hsl(var(--ring));
            box-shadow: 0 0 0 2px hsl(var(--ring) / 0.2);
        }

        .form-input::placeholder {
            color: hsl(var(--placeholder));
        }

        .form-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .checkbox-input {
            width: 1rem;
            height: 1rem;
            border: 1px solid hsl(var(--border));
            border-radius: calc(var(--radius) - 2px);
            margin-right: 0.5rem;
            accent-color: hsl(var(--primary));
        }

        .checkbox-label {
            font-size: 0.875rem;
            color: hsl(var(--card-foreground));
            cursor: pointer;
        }

        /* Button Styles */
        .btn-primary {
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
            background-color: hsl(var(--primary));
            color: hsl(var(--secondary));
            padding: 0.75rem 1rem;
            width: 100%;
            box-sizing: border-box;
        }

        .btn-primary:hover {
            background-color: hsl(var(--primary) / 0.9);
            color: hsl(var(--secondary));
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Alert Styles */
        .alert-error {
            background-color: hsl(var(--destructive) / 0.1);
            color: hsl(var(--destructive));
            border: 1px solid hsl(var(--destructive) / 0.2);
            border-radius: var(--radius);
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
        }

        .alert-error i {
            margin-right: 0.5rem;
        }

        /* Theme Toggle */
        .theme-toggle {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            color: hsl(var(--muted-foreground));
            font-size: 1.125rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: var(--radius);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 10;
        }

        .theme-toggle:hover {
            background-color: hsl(var(--muted));
            color: hsl(var(--foreground));
        }

        /* Links */
        .auth-link {
            color: hsl(var(--accent));
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            transition: color 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .auth-link:hover {
            color: hsl(var(--accent) / 0.8);
        }

        /* Utilities */
        .text-center {
            text-align: center;
        }

        .mt-4 {
            margin-top: 1rem;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .auth-container {
                padding: 1rem;
            }

            .auth-card {
                padding: 1.5rem;
            }

            .auth-brand {
                margin-bottom: 1.5rem;
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
    </style>

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
    </script>
</body>
</html>