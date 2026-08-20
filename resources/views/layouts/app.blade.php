<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EZKIOS - Sistem Manajemen Toko')</title>

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-light-1: #f8fafc;
            --bg-light-2: #f1f5f9;
            --primary-gradient: linear-gradient(to right, #3b82f6, #6366f1);
            --primary-color: #3b82f6;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.4);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light-1);
            color: var(--text-dark);
            margin: 0;
            overflow-x: hidden;
        }

        /* Ambient Background Shapes (Light Theme) */
        .ambient-shape {
            position: fixed;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.5;
            border-radius: 50%;
            animation: float 15s infinite ease-in-out alternate;
        }
        .ambient-shape-1 { width: 500px; height: 500px; background: #bfdbfe; top: -150px; left: 200px; }
        .ambient-shape-2 { width: 600px; height: 600px; background: #ddd6fe; bottom: -200px; right: -50px; animation-delay: -5s; }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(40px, 60px) scale(1.1); }
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* Premium Gradients */
        .bg-gradient-blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important; color: white !important; }
        .bg-gradient-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; color: white !important; }
        .bg-gradient-purple { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%) !important; color: white !important; }
        .bg-gradient-orange { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important; color: white !important; }
        .bg-gradient-red { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important; color: white !important; }
        .bg-gradient-info { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important; color: white !important; }

        /* SIDEBAR STYLES */
        .sidebar {
            width: 260px;
            background: #0f172a; /* Solid dark color */
            border-right: 1px solid var(--glass-border);
            color: #ffffff;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            padding: 24px 20px;
            text-align: center;
            border-bottom: 1px solid var(--glass-border);
        }

        .sidebar-brand h4 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.8rem;
            margin: 0;
            background: linear-gradient(to right, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-brand small {
            color: var(--text-muted);
            font-size: 12px;
        }

        .sidebar-menu {
            padding: 16px 12px;
            flex-grow: 1;
        }

        .sidebar-menu .menu-label {
            padding: 16px 12px 8px;
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 1.5px;
            font-weight: 600;
        }

        .sidebar-menu .nav-link {
            color: #cbd5e1;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 4px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-size: 14px;
            font-weight: 500;
        }

        .sidebar-menu .nav-link i.menu-icon {
            margin-right: 14px;
            font-size: 18px;
            width: 24px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .sidebar-menu .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            transform: translateX(4px);
        }

        .sidebar-menu .nav-link:hover i.menu-icon {
            color: #60a5fa;
            transform: scale(1.1);
        }

        .sidebar-menu .nav-link.active {
            background: var(--primary-gradient);
            color: #ffffff;
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        }

        .sidebar-menu .nav-link.active i.menu-icon {
            color: #ffffff;
        }

        /* MODERN ACCORDION DROPDOWN STYLES */
        .sidebar-menu .nav-link.has-arrow { cursor: pointer; }
        .sidebar-menu .nav-link .arrow-icon { margin-left: auto; font-size: 12px; transition: transform 0.3s ease; }
        .sidebar-menu .nav-link[aria-expanded="true"] .arrow-icon { transform: rotate(90deg); color: #a78bfa; }

        .sidebar-submenu {
            padding-left: 0;
            list-style: none;
            margin: 4px 0 12px 0;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            overflow: hidden;
        }

        .sidebar-submenu .submenu-link {
            display: flex;
            align-items: center;
            padding: 10px 16px 10px 48px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 13.5px;
            transition: all 0.2s;
            position: relative;
        }

        .sidebar-submenu .submenu-link::before {
            content: '';
            position: absolute;
            left: 28px;
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 6px;
            background-color: #475569;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .sidebar-submenu .submenu-link:hover { color: #ffffff; background: rgba(255, 255, 255, 0.05); }
        .sidebar-submenu .submenu-link:hover::before { background-color: #60a5fa; box-shadow: 0 0 8px #60a5fa; }
        .sidebar-submenu .submenu-link.active { color: #ffffff; font-weight: 600; }
        .sidebar-submenu .submenu-link.active::before { background-color: #a78bfa; width: 8px; height: 8px; box-shadow: 0 0 10px #a78bfa; }

        /* USER PROFILE IN SIDEBAR */
        .user-profile {
            padding: 20px;
            border-top: 1px solid var(--glass-border);
            background: rgba(0, 0, 0, 0.2);
        }

        .user-profile .avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .user-profile .user-info { margin-left: 12px; overflow: hidden; }
        .user-profile .user-info .name { font-weight: 600; font-size: 14.5px; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; color: #f8fafc; }
        .user-profile .user-info .role { font-size: 12px; color: #94a3b8; }

        /* MAIN CONTENT STYLES */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px;
            transition: all 0.3s ease;
        }

        /* HYBRID GLASS-LIGHT CARDS (For Readability) */
        .card {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            color: #475569; /* Softer slate instead of harsh black */
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 20px 24px;
            color: #334155;
        }

        .card-header h6, .card-header strong {
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem;
            color: #1e293b;
        }

        /* TABLES IN GLASS CARDS */
        .table {
            color: #475569;
            margin-bottom: 0;
        }
        .table > :not(caption) > * > * {
            background-color: transparent !important; /* Let glassmorphism show through */
            border-bottom-color: rgba(0,0,0,0.05);
            color: #475569;
            padding: 1rem 1.2rem;
        }
        .table th {
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            color: #334155;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: rgba(0, 0, 0, 0.02) !important;
            color: #475569;
        }
        .table-hover > tbody > tr:hover > * {
            background-color: rgba(0, 0, 0, 0.04) !important;
        }

        /* Headings & Page Titles */
        h1, h2, h3, h4, h5 { font-family: 'Outfit', sans-serif; color: var(--text-dark); }
        .page-title { color: var(--text-dark); font-weight: 700; margin-bottom: 0.2rem; }
        .page-description { color: var(--text-muted); margin-bottom: 2rem; }

        /* General Tables inside Light Cards */
        .table { color: #334155; }
        .table thead th {
            background-color: #f8fafc;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        .table td { border-color: #f1f5f9; vertical-align: middle; }

        /* Buttons Update */
        .btn { border-radius: 8px; font-weight: 500; transition: all 0.2s ease; }
        .btn-primary { background: var(--primary-gradient); border: none; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3); }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 10px -1px rgba(59, 130, 246, 0.4); }

        .alert { border-radius: 12px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }

        /* FIX COLOR CONTRASTS */
        .badge:not(.bg-warning):not(.bg-light) { color: #ffffff !important; }
        .text-white, .text-white h1, .text-white h2, .text-white h3, .text-white h4, .text-white h5, .text-white h6 { color: #ffffff !important; }
        .card-header.bg-primary h1, .card-header.bg-primary h2, .card-header.bg-primary h3, .card-header.bg-primary h4, .card-header.bg-primary h5, .card-header.bg-primary h6, .card-header.bg-primary strong,
        .card-header.bg-success h1, .card-header.bg-success h2, .card-header.bg-success h3, .card-header.bg-success h4, .card-header.bg-success h5, .card-header.bg-success h6, .card-header.bg-success strong,
        .card-header.bg-danger h1, .card-header.bg-danger h2, .card-header.bg-danger h3, .card-header.bg-danger h4, .card-header.bg-danger h5, .card-header.bg-danger h6, .card-header.bg-danger strong,
        .card-header.bg-info h1, .card-header.bg-info h2, .card-header.bg-info h3, .card-header.bg-info h4, .card-header.bg-info h5, .card-header.bg-info h6, .card-header.bg-info strong,
        .card-header.bg-dark h1, .card-header.bg-dark h2, .card-header.bg-dark h3, .card-header.bg-dark h4, .card-header.bg-dark h5, .card-header.bg-dark h6, .card-header.bg-dark strong,
        .card-header.text-white h1, .card-header.text-white h2, .card-header.text-white h3, .card-header.text-white h4, .card-header.text-white h5, .card-header.text-white h6, .card-header.text-white strong {
            color: #ffffff !important;
        }

        /* CUSTOM SCROLLBAR */
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
        .sidebar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.4); }

        /* RESPONSIVE DESIGN */
        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content {
                margin-left: 0;
                padding: 16px;
            }
            .wrapper {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Ambient Background Shapes -->
    <div class="ambient-shape ambient-shape-1"></div>
    <div class="ambient-shape ambient-shape-2"></div>

    <div class="wrapper">
        <!-- ========== SIDEBAR KIRI ========== -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h4>EZKIOS</h4>
                <small>{{ __('messages.system_management') }}</small>
            </div>

            <div class="sidebar-menu">
                {{-- <li class="menu-label">Main Menu</li> --}}
                <ul class="list-unstyled">
                    <!-- Semua Role Bisa Lihat Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') || request()->routeIs('owner.dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('kasir.dashboard') ? 'active' : '' }}"
                           href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 menu-icon"></i>
                            <span>{{ __('messages.dashboard') }}</span>
                        </a>
                    </li>

                    @php
                        $user = auth()->user();
                        $isManagementActive = request()->routeIs('users.*') || request()->routeIs('roles.*');
                    @endphp

                    @if($user && $user->hasPermission('view_products'))
                    <li class="nav-item mt-1">
                        <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                            <i class="bi bi-box-seam menu-icon"></i>
                            <span>{{ __('messages.product') }}</span>
                        </a>
                    </li>
                    @endif

                    @if($user && $user->hasPermission('view_suppliers'))
                    <li class="nav-item mt-1">
                        <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                            <i class="bi bi-people menu-icon"></i>
                            <span>{{ __('messages.supplier') }}</span>
                        </a>
                    </li>
                    @endif

                    @if($user && $user->hasPermission('view_purchases'))
                    <li class="nav-item mt-1">
                        <a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}">
                            <i class="bi bi-truck menu-icon"></i>
                            <span>{{ __('messages.purchases') }}</span>
                        </a>
                    </li>
                    @endif

                    @if($user && $user->hasPermission('view_sales'))
                    <li class="nav-item mt-1">
                        <a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">
                            <i class="bi bi-cart3 menu-icon"></i>
                            <span>{{ __('messages.sales') }}</span>
                        </a>
                    </li>
                    @endif

                    @if($user && $user->hasPermission('view_promotions'))
                    <li class="nav-item mt-1">
                        <a class="nav-link {{ request()->routeIs('promotions.*') ? 'active' : '' }}" href="{{ route('promotions.index') }}">
                            <i class="bi bi-tags menu-icon"></i>
                            <span>Promosi</span>
                        </a>
                    </li>
                    @endif

                    @if($user && $user->hasPermission('view_reports'))
                    <li class="nav-item mt-1">
                        <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                            <i class="bi bi-file-earmark-bar-graph menu-icon"></i>
                            <span>{{ __('messages.report') }}</span>
                        </a>
                    </li>
                    @endif

                    @if($user && $user->hasPermission('view_activity_logs'))
                    <li class="nav-item mt-1">
                        <a class="nav-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}" href="{{ route('activity-logs.index') }}">
                            <i class="bi bi-clock-history menu-icon"></i>
                            <span>{{ __('messages.activity_logs') }}</span>
                        </a>
                    </li>
                    @endif

                    @if($user && $user->hasPermission('view_news_events'))
                    <li class="nav-item mt-1">
                        <a class="nav-link {{ request()->routeIs('news-events.*') ? 'active' : '' }}" href="{{ route('news-events.index') }}">
                            <i class="bi bi-megaphone menu-icon"></i>
                            <span>Pengumuman</span>
                        </a>
                    </li>
                    @endif

                    <!-- MANAGEMENT (MODERN COLLAPSIBLE MENU) -->
                    @php
                        $canManageSystem = $user->hasPermission('view_users') ||
                                           $user->hasPermission('view_roles');
                    @endphp
                    @if($user && $canManageSystem)
                    <li class="menu-label mt-2">{{ __('messages.management') }}</li>
                    <li class="nav-item">
                        <a class="nav-link has-arrow {{ $isManagementActive ? 'active' : '' }}"
                           data-bs-toggle="collapse"
                           href="#managementCollapse"
                           role="button"
                           aria-expanded="{{ $isManagementActive ? 'true' : 'false' }}"
                           aria-controls="managementCollapse">
                            <i class="bi bi-gear menu-icon"></i>
                            <span>{{ __('messages.management') }}</span>
                            <i class="bi bi-chevron-right arrow-icon"></i>
                        </a>

                        <div class="collapse {{ $isManagementActive ? 'show' : '' }}" id="managementCollapse">
                            <ul class="sidebar-submenu">
                                @if($user->hasPermission('view_users'))
                                <li>
                                    <a class="submenu-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                        {{ __('messages.user_management') }}
                                    </a>
                                </li>
                                @endif


                                @if($user->hasPermission('view_roles'))
                                <li>
                                    <a class="submenu-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                                        {{ __('messages.role_permissions') }}
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>

                    </li>
                    @endif
                </ul>
            </div>

            <!-- User Profile di Bawah Sidebar -->
            <div class="user-profile mt-auto">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="user-info">
                        <div class="name">{{ Auth::user()->name ?? 'User' }}</div>
                        <div class="role">
                            <i class="bi bi-person-badge"></i>
                            {{ Auth::user()->role->display_name ?? 'No Role' }}
                        </div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-danger btn-sm w-100" type="submit">
                        <i class="bi bi-box-arrow-right"></i> {{ __('messages.logout') }}
                    </button>
                </form>
            </div>
        </aside>

        <!-- ========== MAIN CONTENT ========== -->
        <main class="main-content" id="mainContent">
            <!-- Header / Page Title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="page-title">@yield('page-title', 'Dashboard')</h4>
                    <div class="page-description">@yield('page-subtitle', 'Sistem Manajemen Toko & POS')</div>
                </div>
                <div class="d-flex align-items-center gap-3">

                    <span style="display: inline-block; background: rgba(255,255,255,0.7); color: var(--text-dark); border: 1px solid rgba(0,0,0,0.05); padding: 0.6rem 1rem; border-radius: 12px; backdrop-filter: blur(10px); box-shadow: 0 4px 6px rgba(0,0,0,0.02); font-weight: 500;">
                        <i class="bi bi-calendar3 me-2" style="color: #60a5fa;"></i>
                        {{ date('d F Y') }}
                    </span>
                </div>
            </div>

            <!-- Flash Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Main Dynamic Content -->
            @yield('content')
        </main>
    </div>

    @stack('modals')

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
