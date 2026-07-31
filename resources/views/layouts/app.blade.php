<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Stock Opname') - PT SPINDO Tbk</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-primary: #0a0e17;
            --bg-secondary: #111827;
            --bg-tertiary: #1f2937;
            --bg-card: rgba(17, 24, 39, 0.75);
            --bg-input: #1a2234;
            --border-subtle: rgba(255, 255, 255, 0.08);
            --border-medium: rgba(255, 255, 255, 0.15);
            --border-accent: rgba(245, 158, 11, 0.4);
            --accent-primary: #f59e0b;
            --accent-secondary: #d97706;
            --accent-soft: rgba(245, 158, 11, 0.12);
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
            --text-tertiary: #6b7280;
            --success: #22c55e;
            --success-soft: rgba(34, 197, 94, 0.15);
            --danger: #ef4444;
            --danger-soft: rgba(239, 68, 68, 0.15);
            --font-sans: 'Inter', system-ui, sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 16px;
            --space-xs: 4px;
            --space-sm: 8px;
            --space-md: 12px;
            --space-lg: 16px;
            --space-xl: 24px;
            --shadow-card: 0 8px 32px rgba(0, 0, 0, 0.36);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-family: var(--font-sans);
            min-height: 100vh;
            display: flex;
            justify-content: center;
        }

        #app {
            width: 100%;
            max-width: 480px;
            min-height: 100vh;
            background: var(--bg-secondary);
            display: flex;
            flex-direction: column;
            position: relative;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.8);
        }

        header {
            padding: var(--space-lg);
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-subtle);
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            text-decoration: none;
            color: inherit;
        }

        .brand-icon { font-size: 1.4rem; }
        .brand-title { font-size: 1.05rem; font-weight: 700; color: var(--accent-primary); letter-spacing: -0.2px; }
        .brand-sub { font-size: 0.65rem; color: var(--text-tertiary); display: block; font-weight: 500; }

        main { flex: 1; padding: var(--space-lg); padding-bottom: 80px; }

        .nav-bar {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 480px;
            background: rgba(10, 14, 23, 0.92);
            backdrop-filter: blur(16px);
            border-top: 1px solid var(--border-subtle);
            display: flex;
            justify-content: space-around;
            padding: var(--space-sm) 0;
            z-index: 50;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            color: var(--text-tertiary);
            text-decoration: none;
            font-size: 0.68rem;
            font-weight: 600;
            transition: color 0.2s;
        }

        .nav-item.active, .nav-item:hover { color: var(--accent-primary); }
        .nav-icon { font-size: 1.2rem; }

        .alert {
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            font-size: 0.85rem;
            font-weight: 500;
        }
        .alert-success { background: var(--success-soft); color: var(--success); border: 1px solid rgba(34, 197, 94, 0.3); }

        .card {
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            margin-bottom: var(--space-lg);
            box-shadow: var(--shadow-card);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-sm);
            padding: var(--space-md) var(--space-lg);
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-primary { background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)); color: #000; }
        .btn-success { background: var(--success); color: #000; }
        .btn-block { width: 100%; }

        /* Row calculator styles */
        .row-calc {
            display: flex;
            flex-direction: column;
            gap: var(--space-md);
            padding: var(--space-md);
            background: var(--bg-primary);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
        }
        .row-calc-row { display: flex; align-items: flex-end; gap: var(--space-sm); justify-content: center; }
        .row-calc-field { display: flex; flex-direction: column; align-items: center; gap: 4px; }
        .row-calc-field label { font-size: 0.65rem; font-weight: 600; color: var(--text-tertiary); text-transform: uppercase; }
        .row-calc-input {
            width: 72px; height: 52px; background: var(--bg-input); border: 1px solid var(--border-medium);
            border-radius: var(--radius-md); color: var(--text-primary); font-family: var(--font-mono);
            font-size: 1.5rem; font-weight: 700; text-align: center; outline: none;
        }
        .row-calc-op { font-size: 1.3rem; font-weight: 700; color: var(--text-tertiary); padding-bottom: 12px; }
        .row-calc-total {
            display: flex; align-items: center; justify-content: center; width: 72px; height: 52px;
            background: var(--accent-soft); border: 1px solid var(--border-accent); border-radius: var(--radius-md);
            color: var(--accent-primary); font-family: var(--font-mono); font-size: 1.5rem; font-weight: 800;
        }
        .row-calc-adjust {
            display: flex; align-items: center; gap: var(--space-md); justify-content: center;
            padding-top: var(--space-sm); border-top: 1px solid var(--border-subtle);
        }
        .counter-controls-sm { display: flex; align-items: center; gap: var(--space-sm); }
        .btn-counter-sm {
            width: 38px; height: 38px; border: none; border-radius: var(--radius-sm); font-size: 1.1rem;
            font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center;
        }
        .btn-counter-sm.btn-minus { background: var(--danger-soft); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); }
        .btn-counter-sm.btn-plus { background: var(--success-soft); color: var(--success); border: 1px solid rgba(34, 197, 94, 0.2); }
        .counter-value-sm { font-size: 1.3rem; font-weight: 800; font-family: var(--font-mono); color: var(--text-primary); min-width: 40px; text-align: center; }

        .loose-input {
            width: 120px; height: 52px; background: var(--bg-input); border: 1px solid var(--border-medium);
            border-radius: var(--radius-md); color: var(--text-primary); font-family: var(--font-mono);
            font-size: 1.5rem; font-weight: 700; text-align: center; outline: none;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div id="app">
        <header>
            <a href="{{ route('dashboard') }}" class="brand">
                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMDAgNjAiPjx0ZXh0IHg9IjEwIiB5PSI0NSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXdlaWdodD0iOTAwIiBmb250LXN0eWxlPSJpdGFsaWMiIGZvbnQtc2l6ZT0iNDIiIGZpbGw9IiNEMzJGMkYiIGxldHRlci1zcGFjaW5nPSItMSI+U1BJTkRPPC90ZXh0Pjwvc3ZnPg==" alt="Spindo Logo" style="height: 28px; border-radius: 4px; background: white; padding: 2px;">
                <div>
                    <span class="brand-title">Stock Opname</span>
                    <span class="brand-sub">PT SPINDO Tbk · Unit 7</span>
                </div>
            </a>
            @auth
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="font-size: 0.72rem; color: var(--accent-primary); font-weight: 700; background: var(--accent-soft); padding: 4px 8px; border-radius: 6px; border: 1px solid var(--border-accent);">
                    👷 {{ Auth::user()->name }}
                </div>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" title="Keluar / Logout" style="background: none; border: none; color: var(--danger); cursor: pointer; font-size: 1rem; padding: 4px;">
                        🚪
                    </button>
                </form>
            </div>
            @endauth
        </header>

        <main>
            @if(session('success'))
                <div class="alert alert-success">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </main>

        <nav class="nav-bar">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="nav-icon">🏠</span>
                <span>Home</span>
            </a>
            <a href="{{ route('master.index') }}" class="nav-item {{ request()->routeIs('master.*') ? 'active' : '' }}">
                <span class="nav-icon">⚙️</span>
                <span>Spesifikasi</span>
            </a>
            <a href="{{ route('report.index') }}" class="nav-item {{ request()->routeIs('report.*') ? 'active' : '' }}">
                <span class="nav-icon">📊</span>
                <span>Laporan</span>
            </a>
        </nav>
    </div>

    @stack('scripts')
</body>
</html>
