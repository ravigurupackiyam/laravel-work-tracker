<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Work Timer')</title>

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#6366f1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Work Timer">
    <link rel="icon" href="/icons/icon-192.png" type="image/png">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root {
            --brand-1: #6366f1;
            --brand-2: #8b5cf6;
            --brand-3: #06b6d4;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 50%, #ecfeff 100%);
            min-height: 100vh;
        }
        .navbar-brand-gradient {
            background: linear-gradient(135deg, var(--brand-1), var(--brand-2));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-weight: 700;
        }
        .app-navbar {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(99, 102, 241, 0.1);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 1.25rem;
            box-shadow: 0 10px 30px -12px rgba(99, 102, 241, 0.25);
        }
        .card-header-gradient {
            background: linear-gradient(135deg, var(--brand-1), var(--brand-2));
            color: #fff;
            border-radius: 1.25rem 1.25rem 0 0 !important;
        }
        .btn-gradient {
            background: linear-gradient(135deg, var(--brand-1), var(--brand-2));
            color: #fff;
            border: none;
            font-weight: 600;
            transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
        }
        .btn-gradient:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px -6px rgba(99, 102, 241, 0.6);
            color: #fff;
        }
        .btn-gradient:disabled {
            opacity: .5;
            cursor: not-allowed;
        }
        .btn-end {
            background: linear-gradient(135deg, #f43f5e, #fb7185);
            color: #fff;
            border: none;
            font-weight: 600;
        }
        .btn-end:hover { color: #fff; opacity: .92; }
        .badge-worktype {
            background: linear-gradient(135deg, var(--brand-3), var(--brand-1));
            color: #fff;
            font-weight: 500;
            border-radius: 999px;
            padding: .4em .8em;
        }
        .table > :not(caption) > * > * {
            vertical-align: middle;
        }
        thead.table-head-brand th {
            background: #f5f3ff;
            color: #4c1d95;
            border-bottom: 2px solid #e0d9fb;
            font-weight: 600;
            white-space: nowrap;
        }
        .timer-display {
            font-variant-numeric: tabular-nums;
            font-size: 3rem;
            font-weight: 700;
            letter-spacing: 2px;
            background: linear-gradient(135deg, var(--brand-1), var(--brand-3));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .pulse-dot {
            width: 10px; height: 10px; border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 rgba(34,197,94,.6);
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(34,197,94,.6); }
            70% { box-shadow: 0 0 0 10px rgba(34,197,94,0); }
            100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
        }
        @media (max-width: 576px) {
            .timer-display { font-size: 2.2rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand app-navbar sticky-top py-3">
        <div class="container">
            <a class="navbar-brand navbar-brand-gradient fs-4" href="{{ route('timelogs.index') }}">
                <i class="bi bi-stopwatch-fill"></i> Work Timer
            </a>
            <div class="ms-auto d-flex align-items-center gap-2">
                <button id="pwa-install-btn" class="btn btn-gradient btn-sm rounded-pill px-3 d-none">
                    <i class="bi bi-download"></i> Install App
                </button>
                <a href="{{ route('worktypes.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-tags"></i> Manage Work Types
                </a>
            </div>
        </div>
    </nav>

    <main class="container py-4 py-md-5">
        @if (session('success'))
            <div class="alert alert-success rounded-4 shadow-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger rounded-4 shadow-sm">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" defer></script>

    <script>
        // Register the service worker (enables offline shell + installability)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch((err) => {
                    console.warn('Service worker registration failed:', err);
                });
            });
        }

        // Custom "Install App" button — browsers only fire this event
        // when the app meets install criteria (manifest + service worker + HTTPS)
        let deferredInstallPrompt = null;
        const installBtn = document.getElementById('pwa-install-btn');

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            deferredInstallPrompt = event;
            installBtn.classList.remove('d-none');
        });

        installBtn?.addEventListener('click', async () => {
            if (!deferredInstallPrompt) return;
            deferredInstallPrompt.prompt();
            await deferredInstallPrompt.userChoice;
            deferredInstallPrompt = null;
            installBtn.classList.add('d-none');
        });

        window.addEventListener('appinstalled', () => {
            installBtn?.classList.add('d-none');
        });
    </script>
</body>
</html>
