<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'কৃষি-বন্ধু') | Smart Agriculture Assistant</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Serif+Bengali:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --green-50: #f0faf0; --green-100: #d4f0d4; --green-200: #a8e0a8;
            --green-400: #4caf50; --green-500: #388e3c; --green-600: #2e7d32;
            --green-700: #1b5e20;
            --amber-50: #fffbf0; --amber-100: #fff3cd; --amber-400: #ffc107;
            --sky-50: #f0f8ff; --sky-100: #cce8ff; --sky-400: #2196f3; --sky-500: #1565c0;
            --red-50: #fff5f5; --red-100: #ffcdd2; --red-400: #ef5350; --red-500: #c62828;
            --brown-50: #fdf5f0; --brown-100: #f5ddd0;
            --bg: #f7faf7; --card: #ffffff; --border: #e0ece0;
            --text-primary: #1a2e1a; --text-secondary: #4a6741; --text-muted: #7a9e7a;
            --shadow: 0 2px 8px rgba(46,125,50,0.08);
            --shadow-md: 0 4px 16px rgba(46,125,50,0.12);
            --radius: 12px; --radius-sm: 8px; --radius-lg: 16px;
            --nav-h: 64px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Hind Siliguri', sans-serif; background: var(--bg); color: var(--text-primary); min-height: 100vh; font-size: 16px; line-height: 1.6; }
        h1, h2, h3, h4 { font-family: 'Noto Serif Bengali', serif; font-weight: 600; }
        button { font-family: 'Hind Siliguri', sans-serif; cursor: pointer; border: none; outline: none; transition: all .2s; }
        input, select, textarea { font-family: 'Hind Siliguri', sans-serif; font-size: 15px; }
        a { text-decoration: none; color: inherit; }

        #nav { position: fixed; top: 0; left: 0; right: 0; height: var(--nav-h); background: var(--green-600); color: #fff; z-index: 1000; display: flex; align-items: center; padding: 0 1rem; gap: 1rem; box-shadow: 0 2px 12px rgba(0,0,0,0.15); }
        #nav .logo { display: flex; align-items: center; gap: 8px; font-family: 'Noto Serif Bengali', serif; font-size: 1.25rem; font-weight: 600; flex: 1; }
        #nav .logo span { font-size: 1.5rem; }
        #nav button, #nav a.icon-btn { background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.3); padding: 6px 14px; border-radius: 8px; font-size: 13px; }
        #nav button:hover, #nav a.icon-btn:hover { background: rgba(255,255,255,0.25); }
        #nav #nav-user { font-size: 13px; opacity: .85; max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        #bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; height: 60px; background: var(--card); border-top: 1px solid var(--border); display: grid; z-index: 999; grid-template-columns: repeat(5, 1fr); }
        #bottom-nav a { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px; color: var(--text-muted); font-size: 11px; transition: color .2s; padding: 4px 0; }
        #bottom-nav a.active { color: var(--green-500); }
        #bottom-nav a svg { width: 22px; height: 22px; stroke: currentColor; fill: none; stroke-width: 1.8; }

        #app { padding-top: var(--nav-h); padding-bottom: 70px; min-height: 100vh; }
        .page { padding: 1rem; max-width: 700px; margin: 0 auto; }

        .card { background: var(--card); border-radius: var(--radius); border: 1px solid var(--border); padding: 1.25rem; box-shadow: var(--shadow); margin-bottom: 1rem; }
        .card-sm { padding: .85rem 1rem; }

        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: var(--radius-sm); font-size: 15px; font-weight: 500; transition: all .18s; cursor: pointer; }
        .btn-primary { background: var(--green-500); color: #fff; }
        .btn-primary:hover { background: var(--green-600); }
        .btn-secondary { background: var(--green-50); color: var(--green-600); border: 1px solid var(--green-200); }
        .btn-secondary:hover { background: var(--green-100); }
        .btn-danger { background: var(--red-50); color: var(--red-500); border: 1px solid var(--red-100); }
        .btn-block { width: 100%; justify-content: center; }
        .btn:disabled { opacity: .5; cursor: not-allowed; }

        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-size: 14px; font-weight: 500; color: var(--text-secondary); margin-bottom: 5px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); background: #fff; color: var(--text-primary); font-size: 15px; transition: border-color .2s; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--green-400); outline: none; }
        .form-group textarea { resize: vertical; min-height: 90px; }
        .form-group .error-text { color: var(--red-500); font-size: 12px; margin-top: 4px; }

        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 500; }
        .badge-green { background: var(--green-100); color: var(--green-600); }
        .badge-amber { background: var(--amber-100); color: #7a5200; }
        .badge-red { background: var(--red-100); color: var(--red-500); }
        .badge-sky { background: var(--sky-100); color: var(--sky-500); }

        .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: .75rem; }

        .page-title { font-size: 1.3rem; color: var(--green-700); margin-bottom: 1rem; padding-bottom: .5rem; border-bottom: 2px solid var(--green-100); }
        .section-title { font-size: 1rem; color: var(--text-secondary); font-weight: 600; margin: .75rem 0 .5rem; font-family: 'Hind Siliguri', sans-serif; }

        .alert { padding: .85rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1rem; font-size: 14px; }
        .alert-success { background: var(--green-50); border: 1px solid var(--green-200); color: var(--green-700); }
        .alert-error { background: var(--red-50); border: 1px solid var(--red-100); color: var(--red-500); }

        @media (max-width: 480px) { .grid-3 { grid-template-columns: repeat(2, 1fr); } }
    </style>
    @stack('styles')
</head>
<body>
    @auth
    <nav id="nav">
        <div class="logo"><span>🌾</span> কৃষি-বন্ধু</div>
        <span id="nav-user">{{ auth()->user()->name }}</span>
        <a href="{{ route('notifications.index') }}" class="icon-btn" title="বিজ্ঞপ্তি">🔔</a>
        <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
            @csrf
            <button type="submit">বের হন</button>
        </form>
    </nav>

    <div id="app">
        <div class="page">
            @if(session('success'))
                <div class="alert alert-success">✅ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">⚠️ {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error">
                    @foreach($errors->all() as $error)
                        <div>⚠️ {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <nav id="bottom-nav">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            হোম
        </a>
        <a href="{{ route('scan.index') }}" class="{{ request()->routeIs('scan.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            স্ক্যান
        </a>
        <a href="{{ route('prices.index') }}" class="{{ request()->routeIs('prices.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            বাজার
        </a>
        <a href="{{ route('market.index') }}" class="{{ request()->routeIs('market.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            মার্কেট
        </a>
        <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            প্রোফাইল
        </a>
    </nav>
    @else
        @yield('content')
    @endauth

    @stack('scripts')
</body>
</html>
