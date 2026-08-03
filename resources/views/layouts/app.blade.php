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

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
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
