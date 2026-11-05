<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $title ?? config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --app-primary: #4F46E5;
            --app-primary-hover: #4338CA;
            --app-primary-soft: #EEF2FF;
            --app-secondary: #06B6D4;
            --app-accent: #F59E0B;
            --app-surface: #ffffff;
            --app-body: #EEF4FF;
            --app-border: rgba(226, 232, 240, 0.8);
            --app-text: #0F172A;
            --app-muted: #64748B;
            --app-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.08);
            --app-shadow-soft: 0 4px 6px -1px rgba(15, 23, 42, 0.05), 0 2px 4px -2px rgba(15, 23, 42, 0.05);
            --app-radius: 20px;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(79, 70, 229, 0.14), transparent 28%),
                radial-gradient(circle at top right, rgba(6, 182, 212, 0.12), transparent 24%),
                linear-gradient(180deg, #F4F7FF 0%, var(--app-body) 100%);
            color: var(--app-text); 
            -webkit-font-smoothing: antialiased;
        }
        .topbar {
            height: 76px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--app-border);
            z-index: 1030;
        }
        .brand-title { color: var(--app-text); font-size: 17px; font-weight: 700; text-decoration: none; line-height: 1.2; letter-spacing: -0.01em; }
        .brand-subtitle { color: var(--app-muted); font-size: 12.5px; font-weight: 500; }
        .brand-logo { height: 40px; width: auto; }
        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.98);
            border-right: 1px solid var(--app-border);
            z-index: 1020;
        }
        .content-wrap { margin-left: 280px; padding-top: 76px; min-height: 100vh; }
        .page-content { padding: 32px 40px; max-width: 1600px; margin: 0 auto; }
        .section-label {
            color: #94A3B8;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 11px;
            font-weight: 700;
            padding: 20px 24px 10px;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 14px;
            color: #475569;
            text-decoration: none;
            padding: 12px 20px;
            margin: 4px 16px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 14.5px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-link i { width: 20px; height: 20px; stroke-width: 2.2px; }
        .sidebar-link:hover { 
            background: var(--app-primary-soft); 
            color: var(--app-primary); 
            transform: translateX(4px); 
        }
        .sidebar-link.active {
            background: var(--app-primary);
            color: #ffffff;
            box-shadow: 0 8px 16px -4px rgba(79, 70, 229, 0.3);
        }
        .sidebar-link.active i { stroke: #ffffff; }
        .sidebar-link .menu-badge { margin-left: auto; }
        .avatar-circle,
        .avatar-photo {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .avatar-circle { background: var(--app-primary-soft); color: var(--app-primary); font-weight: 700; font-size: 15px; }
        .avatar-photo { object-fit: cover; border: 2px solid #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        
        /* Premium Cards */
        .content-card {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.75);
            border-radius: var(--app-radius);
            box-shadow:
                0 22px 45px -24px rgba(79, 70, 229, 0.32),
                0 18px 30px -24px rgba(15, 23, 42, 0.28);
            background:
                linear-gradient(145deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.94));
            transition: all 0.3s ease;
        }
        .content-card::before {
            content: "";
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--app-primary), var(--app-secondary), var(--app-accent));
        }
        .content-card::after {
            content: "";
            position: absolute;
            right: -50px;
            top: -55px;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.14), transparent 68%);
            pointer-events: none;
        }
        .content-card:hover {
            transform: translateY(-4px);
            box-shadow:
                0 30px 55px -25px rgba(79, 70, 229, 0.38),
                0 22px 40px -28px rgba(15, 23, 42, 0.32);
            border-color: rgba(99, 102, 241, 0.24);
        }
        .card-header { background: transparent !important; border-bottom: 1px solid var(--app-border) !important; padding: 20px 24px !important; }
        .card-body { padding: 24px !important; }
        
        .footer-app {
            background: transparent;
            font-size: 13px;
            font-weight: 500;
            color: var(--app-muted);
            text-align: center;
            padding: 24px;
        }
        
        /* Badges */
        .badge { padding: 6px 12px; font-weight: 600; border-radius: 8px; letter-spacing: 0.02em; }
        .badge-soft-purple { background: var(--app-primary-soft); color: var(--app-primary); }
        .badge-soft-green { background: #ECFDF5; color: #059669; }
        .badge-soft-yellow { background: #FFFBEB; color: #D97706; }
        .badge-soft-red { background: #FEF2F2; color: #DC2626; }
        .badge-soft-gray { background: #F1F5F9; color: #475569; }
        
        /* Buttons */
        .btn {
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 12px;
            transition: all 0.2s ease;
            letter-spacing: 0.01em;
        }
        .app-btn-primary, .btn-primary {
            background: var(--app-primary);
            border-color: var(--app-primary);
            color: #fff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }
        .app-btn-primary:hover, .btn-primary:hover {
            background: var(--app-primary-hover);
            border-color: var(--app-primary-hover);
            color: #fff;
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.3);
            transform: translateY(-1px);
        }
        .btn-outline-secondary { border-color: #CBD5E1; color: #475569; }
        .btn-outline-secondary:hover { background: #F1F5F9; border-color: #94A3B8; color: #0F172A; }
        
        /* Forms */
        .form-control, .form-select {
            border: 1px solid #CBD5E1;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 500;
            background-color: #F8FAFC;
            transition: all 0.2s ease;
        }
        .form-control:focus, .form-select:focus {
            background-color: #FFFFFF;
            border-color: var(--app-primary);
            box-shadow: 0 0 0 4px var(--app-primary-soft);
        }
        .select2-container .select2-selection--single {
            height: 48px !important;
            padding: 10px 14px !important;
            border: 1px solid #CBD5E1 !important;
            border-radius: 12px !important;
            background-color: #F8FAFC !important;
            font-weight: 500;
        }
        
        /* Topbar specific */
        .topbar-toggle, .topbar-action {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            border: 1px solid var(--app-border);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            background: #ffffff;
            transition: all 0.2s ease;
        }
        .topbar-toggle:hover, .topbar-action:hover { background: #F1F5F9; color: var(--app-primary); }
        .profile-trigger { color: var(--app-text); text-decoration: none; padding: 6px 16px 6px 6px; border-radius: 50px; border: 1px solid transparent; transition: all 0.2s; }
        .profile-trigger:hover { background: #F1F5F9; border-color: var(--app-border); }
        
        /* Tables */
        .table { --bs-table-bg: transparent; margin-bottom: 0; }
        .table > :not(caption) > * > * { 
            padding: 16px 20px; 
            border-bottom-color: var(--app-border); 
            color: var(--app-text);
            font-size: 14.5px;
        }
        .table > thead > tr > th { 
            font-weight: 600; 
            text-transform: uppercase; 
            font-size: 12px; 
            letter-spacing: 0.05em; 
            color: var(--app-muted);
            border-bottom: 2px solid var(--app-border);
            background: #F8FAFC;
        }
        .table-hover > tbody > tr:hover > * {
            background-color: #F8FAFC;
        }
        
        @media (min-width: 992px) {
            .sidebar {
                min-height: calc(100vh - 76px);
                position: fixed;
                top: 76px;
                left: 0;
                bottom: 0;
                overflow-y: auto;
                visibility: visible !important;
                transform: none !important;
            }
        }
        @media (max-width: 991.98px) {
            .content-wrap { margin-left: 0; padding-top: 76px; }
            .page-content { padding: 24px; }
            .sidebar { width: min(320px, 88vw); }
        }
        @media (max-width: 767.98px) {
            .page-content { padding: 16px; }
            .brand-subtitle { display: none; }
        }
        @media print {
            .sidebar, .topbar, .print-hide, .footer-app { display: none !important; }
            .content-wrap { margin-left: 0; padding-top: 0; }
            .page-content { padding: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @php
        $user = auth()->user();
        $isAdmin = $user?->isAdmin() ?? false;
        $canManageMembersMenu = $user?->canManageMembersMenu() ?? false;
        $homeRoute = $user?->homeRoute() ?? 'login';
        $roleLabel = $isAdmin ? 'Administrator / Guru' : 'Siswa';

        // Setiap item menu bisa punya 'active_patterns' (array route/pattern) untuk
        // menandai kapan menu tersebut dianggap "aktif". Kalau tidak diisi, defaultnya
        // hanya cocok persis dengan route utamanya (tanpa wildcard otomatis), supaya
        // menu seperti "Pinjam Buku" & "Pinjaman Saya" tidak aktif bersamaan.
        $menuSections = $isAdmin
            ? [
                'Utama' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home'],
                    ['label' => 'History E-Book', 'route' => 'admin.ebooks.history', 'icon' => 'bar-chart-2'],
                    ['label' => 'Laporan', 'route' => 'admin.reports.index', 'icon' => 'file-text'],
                ],
                'Manajemen Perpustakaan' => array_values(array_filter([
                    [
                        'label' => 'Data Buku',
                        'route' => 'admin.books.index',
                        'icon' => 'book',
                        'active_patterns' => ['admin.books.*'],
                    ],
                    $canManageMembersMenu ? [
                        'label' => 'Kelola Anggota',
                        'route' => 'admin.members.index',
                        'icon' => 'users',
                        'active_patterns' => ['admin.members.*'],
                    ] : null,
                    [
                        'label' => 'Peminjaman',
                        'route' => 'admin.loans.index',
                        'icon' => 'book-open',
                        'active_patterns' => ['admin.loans.*'],
                    ],
                    [
                        'label' => 'Kartu Anggota',
                        'route' => 'admin.cards.index',
                        'icon' => 'credit-card',
                        'active_patterns' => ['admin.cards.*'],
                    ],
                ])),
                'Tools' => [
                    ['label' => 'Pengaturan', 'route' => 'admin.settings.edit', 'icon' => 'settings', 'active_patterns' => ['admin.settings.*']],
                ],
            ]
            : [
                'Utama' => [
                    ['label' => 'Beranda', 'route' => 'siswa.dashboard', 'icon' => 'home'],
                    ['label' => 'Katalog Buku', 'route' => 'siswa.books.index', 'icon' => 'book-open'],
                    ['label' => 'E-Book', 'route' => 'ebooks.index', 'icon' => 'monitor'],
                ],
                'Aktivitas' => [
                    [
                        'label' => 'Pinjam Buku',
                        'route' => 'siswa.loans.create',
                        'icon' => 'plus-circle',
                        // exact match saja, jangan sampai cocok dengan siswa.loans.index
                        'active_patterns' => ['siswa.loans.create'],
                    ],
                    [
                        'label' => 'Pinjaman Saya',
                        'route' => 'siswa.loans.index',
                        'icon' => 'archive',
                        // exact match saja, jangan sampai cocok dengan siswa.loans.create
                        'active_patterns' => ['siswa.loans.index'],
                    ],
                    [
                        'label' => 'Kartu Anggota',
                        'route' => 'siswa.card.download',
                        'icon' => 'credit-card',
                        'active_patterns' => ['siswa.card.download'],
                    ],
                ],
                'Tools' => [
                    ['label' => 'Chatbot', 'route' => 'chatbot.index', 'icon' => 'message-circle'],
                ],
            ];
    @endphp
    @php
        $school = config('perpustakaan.school');
    @endphp

    <nav class="navbar navbar-expand topbar fixed-top">
        <div class="container-fluid px-3 px-lg-4">
            <div class="d-flex align-items-center gap-3">
                <button class="btn topbar-toggle d-lg-none p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar" aria-label="Buka menu">
                    <i data-feather="menu" style="color: #475569;"></i>
                </button>
                <a class="brand-title d-flex align-items-center gap-3" href="{{ route($homeRoute) }}">
                    <img src="{{ asset($school['logo']) }}" alt="Logo {{ $school['short_name'] }}" class="brand-logo">
                    <span>
                        <span class="d-block">{{ $school['short_name'] }}</span>
                        <span class="brand-subtitle">{{ $school['library_name'] }}</span>
                    </span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2">
                @auth
                    @include('partials.notification-bell')
                @endauth
                <div class="dropdown">
                    <button class="btn profile-trigger d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                        @if ($user?->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="avatar-photo" style="width:40px;height:40px;">
                        @else
                            <span class="avatar-circle" style="width:40px;height:40px;">{{ $user?->initials }}</span>
                        @endif
                        <span class="d-none d-md-block text-start">
                            <span class="d-block fw-semibold">{{ $user?->name }}</span>
                            <span class="small text-muted">{{ $roleLabel }}</span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profil Saya</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <aside class="sidebar offcanvas-lg offcanvas-start print-hide" tabindex="-1" id="appSidebar" aria-labelledby="appSidebarLabel">
        <div class="offcanvas-header d-lg-none border-bottom">
            <div>
                <div class="fw-semibold" id="appSidebarLabel">Menu Navigasi</div>
                <div class="small text-muted">Akses cepat fitur perpustakaan</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar" aria-label="Tutup"></button>
        </div>
        <div class="offcanvas-body p-0 d-block">
            <div class="p-4 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    @if ($user?->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="avatar-photo">
                    @else
                        <div class="avatar-circle">{{ $user?->initials }}</div>
                    @endif
                    <div class="min-w-0">
                        <div class="fw-semibold text-truncate">{{ $user?->name }}</div>
                        <div class="small text-muted mb-2">{{ $user?->displayClass() }}</div>
                        <span class="badge {{ $isAdmin ? 'badge-soft-purple' : 'badge-soft-green' }}">{{ $roleLabel }}</span>
                    </div>
                </div>
            </div>

            @foreach ($menuSections as $sectionLabel => $items)
                <div class="section-label">{{ $sectionLabel }}</div>
                <div class="pb-1">
                    @foreach ($items as $item)
                        @php
                            // Kalau item tidak mendefinisikan 'active_patterns', fallback ke exact match route-nya saja.
                            $patterns = $item['active_patterns'] ?? [$item['route']];
                            $active = request()->routeIs($patterns);
                        @endphp
                        <a href="{{ route($item['route']) }}" class="sidebar-link {{ $active ? 'active' : '' }}">
                            <i data-feather="{{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                            @if (!empty($item['badge']))
                                <span class="badge text-bg-success menu-badge">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endforeach
        </div>
    </aside>

    <div class="content-wrap">
        <div class="page-content">
            @if (!empty($breadcrumb))
                <nav aria-label="breadcrumb" class="mb-3 print-hide">
                    <ol class="breadcrumb small mb-0">
                        @foreach ($breadcrumb as $item)
                            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">{{ $item }}</li>
                        @endforeach
                    </ol>
                </nav>
            @endif

            @if (session('success'))
                <div class="alert alert-success print-hide">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger print-hide">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger print-hide">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
        <footer class="footer-app">{{ $school['short_name'] }} - {{ $school['library_name'] }} © 2026</footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/feather-icons/dist/feather.min.js"></script>
    <script>feather.replace();</script>
    @stack('scripts')
</body>
</html>