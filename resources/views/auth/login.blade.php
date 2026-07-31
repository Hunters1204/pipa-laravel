<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Login Petugas - PT SPINDO Tbk</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-primary: #0a0e17;
            --bg-secondary: #111827;
            --bg-card: rgba(17, 24, 39, 0.85);
            --bg-input: #1a2234;
            --border-subtle: rgba(255, 255, 255, 0.08);
            --border-medium: rgba(255, 255, 255, 0.15);
            --border-accent: rgba(245, 158, 11, 0.4);
            --accent-primary: #f59e0b;
            --accent-secondary: #d97706;
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
            --text-tertiary: #6b7280;
            --danger: #ef4444;
            --danger-soft: rgba(239, 68, 68, 0.15);
            --font-sans: 'Inter', system-ui, sans-serif;
            --radius-md: 10px;
            --radius-lg: 16px;
            --space-md: 12px;
            --space-lg: 16px;
            --space-xl: 24px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-family: var(--font-sans);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-lg);
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: var(--space-xl);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6);
        }

        .header {
            text-align: center;
            margin-bottom: var(--space-xl);
        }
        .header-icon { font-size: 3rem; margin-bottom: var(--space-sm); }
        .header-title { font-size: 1.4rem; font-weight: 800; color: var(--accent-primary); }
        .header-sub { font-size: 0.8rem; color: var(--text-tertiary); margin-top: 4px; }

        .quick-users {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: var(--space-xl);
        }

        .user-btn {
            background: var(--bg-input);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: 10px 4px;
            color: var(--text-primary);
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .user-btn:hover, .user-btn.active {
            border-color: var(--accent-primary);
            background: rgba(245, 158, 11, 0.12);
        }

        .user-btn-avatar { font-size: 1.2rem; display: block; margin-bottom: 2px; }
        .user-btn-name { font-size: 0.72rem; font-weight: 700; }

        .form-group {
            margin-bottom: var(--space-lg);
        }

        .form-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-tertiary);
            text-transform: uppercase;
            margin-bottom: 6px;
            display: block;
        }

        .form-input {
            width: 100%;
            padding: var(--space-md);
            background: var(--bg-input);
            border: 1px solid var(--border-medium);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-size: 0.9rem;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--accent-primary);
        }

        .btn-submit {
            width: 100%;
            padding: var(--space-md);
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: #000;
            font-weight: 800;
            font-size: 1rem;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            margin-top: var(--space-md);
        }

        .alert-error {
            background: var(--danger-soft);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: var(--space-md);
            border-radius: var(--radius-md);
            font-size: 0.8rem;
            margin-bottom: var(--space-lg);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="header">
            <img src="{{ asset('images/spindo-logo.png') }}" alt="Spindo Logo" style="height: 60px; margin-bottom: var(--space-sm);">
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

    <script>
        function selectUser(email, el) {
            document.getElementById('emailInput').value = email;
            document.getElementById('passwordInput').value = ''; // Kosongkan password agar diisi manual
            document.getElementById('passwordInput').focus();
            document.querySelectorAll('.user-btn').forEach(btn => btn.classList.remove('active'));
            el.classList.add('active');
        }

        document.getElementById('togglePasswordBtn').addEventListener('click', function() {
            const pwd = document.getElementById('passwordInput');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                this.innerHTML = '👀'; 
            } else {
                pwd.type = 'password';
                this.innerHTML = '👁️';
            }
        });
    </script>
</body>
</html>
