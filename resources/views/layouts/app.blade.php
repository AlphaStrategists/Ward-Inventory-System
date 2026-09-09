<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ward Inventory Management System')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background: #f4f6fb;
            color: #1f2937;
        }

        /* ===== TOP NAVBAR ===== */
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            padding: 14px 28px;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }

        .navbar__brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .navbar__logo-icon {
            width: 28px;
            height: 28px;
            color: #2b3fd6;
        }

        .navbar__titles {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .navbar__title {
            font-size: 18px;
            font-weight: 800;
            color: #2b3fd6;
        }

        .navbar__subtitle {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.6px;
            color: #9ca3af;
        }

        .navbar__nav {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link {
            text-decoration: none;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 600;
            color: #4b5563;
            border-radius: 8px;
            transition: all 0.15s ease;
        }

        .nav-link:hover, .nav-link--active {
            background: #eef2ff;
            color: #2b3fd6;
        }

        /* ===== PAGE LAYOUT ===== */
        .layout {
            display: flex;
            min-height: calc(100vh - 60px);
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 260px;
            background: #fff;
            padding: 20px 16px;
            border-right: 1px solid #e5e7eb;
            flex-shrink: 0;
        }

        .sidebar__title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #9ca3af;
            margin-bottom: 12px;
            padding-left: 4px;
        }

        .sidebar__menu {
            list-style: none;
            margin-bottom: 24px;
        }

        .sidebar__item {
            margin-bottom: 4px;
        }

        .sidebar__link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: #4b5563;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.15s ease;
        }

        .sidebar__link:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .sidebar__link--active {
            background: #eef2ff;
            color: #2b3fd6;
            font-weight: 700;
        }

        /* ===== MAIN CONTENT ===== */
        .content {
            flex: 1;
            padding: 28px;
            max-width: 100%;
            overflow-x: hidden;
        }

        /* ===== ALERTS ===== */
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .alert--success {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .alert--danger {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        /* ===== STOCK HEADER (blue bar) ===== */
        .stock-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #1e3a8a;
            border-radius: 14px;
            padding: 24px 28px;
            color: #fff;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .stock-header__title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stock-header__subtitle {
            font-size: 13px;
            opacity: 0.85;
        }

        .stock-header__balance {
            background: #fef3c7;
            border-radius: 10px;
            padding: 10px 22px;
            text-align: center;
        }

        .stock-header__qty {
            display: block;
            font-size: 22px;
            font-weight: 700;
            color: #92400e;
        }

        .stock-header__label {
            display: block;
            font-size: 11px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #92400e;
        }

        /* ===== CARD ===== */
        .card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .card__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .card__title {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }

        .card__body {
            padding: 24px;
        }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-primary { background: #2b3fd6; color: #fff; }
        .btn-primary:hover { background: #1d3fae; }
        .btn-success { background: #16a34a; color: #fff; }
        .btn-success:hover { background: #15803d; }
        .btn-warning { background: #d97706; color: #fff; }
        .btn-warning:hover { background: #b45309; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .btn-secondary:hover { background: #d1d5db; }
        .btn-danger { background: #e11d2e; color: #fff; }
        .btn-danger:hover { background: #b91c1c; }

        /* ===== TABLE ===== */
        .log-table {
            width: 100%;
            border-collapse: collapse;
        }

        .log-table th {
            text-align: left;
            font-size: 11px;
            letter-spacing: 0.5px;
            color: #6b7280;
            text-transform: uppercase;
            padding: 14px 20px;
            border-bottom: 1px solid #e5e7eb;
            background: #fafafa;
        }

        .log-table td {
            padding: 16px 20px;
            font-size: 13px;
            color: #1f2937;
            border-bottom: 1px solid #f1f2f5;
        }

        .log-table tr:hover td {
            background: #f9fafb;
        }

        /* ===== BADGES ===== */
        .badge {
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 999px;
        }
        .badge--pending { background: #fef3c7; color: #b45309; }
        .badge--approved { background: #dbeafe; color: #1d4ed8; }
        .badge--issued { background: #e0e7ff; color: #4338ca; }
        .badge--completed { background: #dcfce7; color: #15803d; }
        .badge--low_stock { background: #fee2e2; color: #dc2626; }

        /* Stock Quantity Badges */
        .qty--good    { background: #dcfce7; color: #16a34a; }
        .qty--warning { background: #fef3c7; color: #b45309; }
        .qty--danger  { background: #fee2e2; color: #dc2626; }
        .qty--neutral { background: #eef0f4; color: #6b7280; }

        /* FORMS */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 700;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .form-control {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            outline: none;
            background: #fff;
            width: 100%;
        }

        .form-control:focus {
            border-color: #2b3fd6;
            box-shadow: 0 0 0 3px rgba(43, 63, 214, 0.1);
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- TOP NAVBAR -->
    <header class="navbar">
        <a href="{{ route('dashboard') }}" class="navbar__brand">
            <svg class="navbar__logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M2 12h4l2 8 4-16 2 8h8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <div class="navbar__titles">
                <span class="navbar__title">Hospital Ward Inventory</span>
                <span class="navbar__subtitle">Ward 47 & 48 Management System</span>
            </div>
        </a>

        <nav class="navbar__nav">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link--active' : '' }}">Dashboard</a>
            <a href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'nav-link--active' : '' }}">Stock Catalog</a>
            <a href="{{ route('requested-stock.index') }}" class="nav-link {{ request()->routeIs('requested-stock.*') ? 'nav-link--active' : '' }}">Stock Requisitions</a>
            <a href="{{ route('narcotic-usage.index') }}" class="nav-link {{ request()->routeIs('narcotic-usage.*') ? 'nav-link--active' : '' }}">Narcotic Log</a>
            <a href="{{ route('consumable-usage.index') }}" class="nav-link {{ request()->routeIs('consumable-usage.*') ? 'nav-link--active' : '' }}">Consumable Log</a>
            <a href="{{ route('general-inventory.index') }}" class="nav-link {{ request()->routeIs('general-inventory.*') ? 'nav-link--active' : '' }}">General Inventory</a>
        </nav>
    </header>

    <div class="layout">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar__title">Navigation</div>
            <ul class="sidebar__menu">
                <li class="sidebar__item">
                    <a href="{{ route('dashboard') }}" class="sidebar__link {{ request()->routeIs('dashboard') ? 'sidebar__link--active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Dashboard
                    </a>
                </li>
                <li class="sidebar__item">
                    <a href="{{ route('items.index') }}" class="sidebar__link {{ request()->routeIs('items.*') ? 'sidebar__link--active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                        Items Catalog
                    </a>
                </li>
                <li class="sidebar__item">
                    <a href="{{ route('requested-stock.index') }}" class="sidebar__link {{ request()->routeIs('requested-stock.*') ? 'sidebar__link--active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Stock Requisitions
                    </a>
                </li>
                <li class="sidebar__item">
                    <a href="{{ route('narcotic-usage.index') }}" class="sidebar__link {{ request()->routeIs('narcotic-usage.*') ? 'sidebar__link--active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Narcotic Usage Log
                    </a>
                </li>
                <li class="sidebar__item">
                    <a href="{{ route('consumable-usage.index') }}" class="sidebar__link {{ request()->routeIs('consumable-usage.*') ? 'sidebar__link--active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                        Consumable Usage
                    </a>
                </li>
                <li class="sidebar__item">
                    <a href="{{ route('general-inventory.index') }}" class="sidebar__link {{ request()->routeIs('general-inventory.*') ? 'sidebar__link--active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        General Inventory
                    </a>
                </li>
            </ul>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="content">
            @if(session('success'))
                <div class="alert alert--success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert--danger">
                    <ul style="margin-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</body>
</html>
