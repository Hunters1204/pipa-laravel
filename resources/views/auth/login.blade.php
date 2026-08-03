<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Login Petugas - PT SPINDO Tbk</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="login-card">
        <div class="header">
            <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMDAgNjAiPjx0ZXh0IHg9IjEwIiB5PSI0NSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXdlaWdodD0iOTAwIiBmb250LXN0eWxlPSJpdGFsaWMiIGZvbnQtc2l6ZT0iNDIiIGZpbGw9IiNEMzJGMkYiIGxldHRlci1zcGFjaW5nPSItMSI+U1BJTkRPPC90ZXh0Pjwvc3ZnPg==" alt="Spindo Logo" style="height: 40px; margin-bottom: var(--space-sm);">
            <h1 class="header-title">Stock Opname</h1>
            <p class="header-sub">PT SPINDO Tbk · Unit 7 Gresik</p>
        </div>

        @if($errors->any())
            <div class="alert-error">
                ⚠️ {{ $errors->first() }}
            </div>
        @endif

        <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-tertiary); text-transform: uppercase; margin-bottom: 8px; text-align: center;">
            Pilih Petugas (Quick Select):
        </div>

        <div class="quick-users">
            @foreach($users as $u)
                <div class="user-btn" onclick="selectUser('{{ $u->email }}', this)">
                    <span class="user-btn-avatar">👷</span>
                    <span class="user-btn-name">{{ $u->name }}</span>
                </div>
            @endforeach
        </div>

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Email Petugas</label>
                <input type="email" name="email" id="emailInput" class="form-input" placeholder="pilih atau ketik email..." value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="passwordInput" class="form-input" placeholder="••••••••" required>
                    <button type="button" id="togglePasswordBtn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 1.1rem; cursor: pointer; color: var(--text-tertiary); padding: 4px;" title="Tampilkan/Sembunyikan Password">
                        👁️
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                Masuk Sistem ➔
            </button>
        </form>
    </div>

    <script src="{{ asset('js/login.js') }}"></script>
</body>
</html>
