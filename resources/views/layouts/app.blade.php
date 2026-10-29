<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ward Inventory System') - Ward 48</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Font Awesome 6.5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --bs-font-sans-serif: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --primary-blue: #2563eb;
            --primary-blue-hover: #1d4ed8;
            --primary-blue-gradient: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%);
            --sidebar-bg: #ffffff;
            --sidebar-border: #e2e8f0;
            --content-bg: #f8fafc;
            --card-border: #edf2f7;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --active-item-bg: #eff6ff;
            --active-item-border: #2563eb;
            --success-green: #16a34a;
            --success-green-bg: #f0fdf4;
            --danger-red: #dc2626;
            --danger-red-bg: #fef2f2;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: var(--bs-font-sans-serif);
            background-color: var(--content-bg);
            color: var(--text-dark);
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        /* Top System Navigation Bar */
        .top-navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--sidebar-border);
            padding: 0.75rem 1.5rem;
            position: relative;
            flex: 0 0 auto;
            top: 0;
            z-index: 1030;
            backdrop-filter: blur(8px);
        }

        .app-body {
            flex: 1;
            min-height: 0;
            /* required so children can scroll */
            display: flex;
            width: 100%;
        }

        .nav-pill-link {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-muted);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-pill-link:hover {
            color: var(--primary-blue);
            background: var(--active-item-bg);
        }

        .nav-pill-link.active {
            color: #ffffff;
            background: var(--primary-blue);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        /* Sidebar Styling */
        .inventory-sidebar {
            width: 330px;
            min-width: 330px;
            max-width: 330px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            height: 100%;
            /* was calc(100vh - 65px) */
            position: static;
            top: 65px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-brand-header {
            padding: 1.5rem 1.5rem 1rem;
        }

        .sidebar-search-box {
            position: relative;
            padding: 0 1.5rem 1rem;
        }

        .sidebar-search-box .bi-search {
            position: absolute;
            left: 2.25rem;
            top: 45%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .sidebar-search-input {
            padding-left: 2.5rem;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .sidebar-search-input:focus {
            background-color: #ffffff;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .sidebar-action-btn {
            margin: 0 1.5rem 1rem;
            background-color: var(--primary-blue);
            color: #ffffff;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.28);
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .sidebar-action-btn:hover {
            background-color: var(--primary-blue-hover);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35);
        }

        .sidebar-list-container {
            flex: 1;
            overflow-y: auto;
            padding: 0.5rem 1rem 1.5rem;
        }

        /* Sidebar Item Card */
        .sidebar-item-card {
            display: block;
            text-decoration: none;
            color: var(--text-dark);
            padding: 0.85rem 1rem;
            margin-bottom: 0.45rem;
            border-radius: 12px;
            transition: all 0.18s ease;
            position: relative;
            background: #ffffff;
            border: 1px solid transparent;
        }

        .sidebar-item-card:hover {
            background-color: #f1f5f9;
            color: var(--text-dark);
        }

        .sidebar-item-card.active {
            background-color: var(--active-item-bg);
            border: 1px solid #bfdbfe;
            border-left: 4px solid var(--active-item-border);
        }

        .sidebar-item-name {
            font-size: 0.925rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            margin-bottom: 0.25rem;
            color: #0f172a;
        }

        .sidebar-item-card.active .sidebar-item-name {
            color: #1e40af;
        }

        .sidebar-item-badge {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.3rem 0.65rem;
            border-radius: 8px;
            background-color: #f1f5f9;
            color: #475569;
        }

        .sidebar-item-card.active .sidebar-item-badge {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .sidebar-item-actions {
            opacity: 0.4;
            transition: opacity 0.2s;
            display: inline-flex;
            gap: 0.5rem;
        }

        .sidebar-item-card:hover .sidebar-item-actions,
        .sidebar-item-card.active .sidebar-item-actions {
            opacity: 1;
        }

        .action-icon-btn {
            background: transparent;
            border: none;
            padding: 0;
            color: #64748b;
            font-size: 0.85rem;
            cursor: pointer;
            transition: color 0.15s;
        }

        .action-icon-btn:hover {
            color: var(--primary-blue);
        }

        /* Main Content Container */
        .main-content-panel {
            flex: 1;
            padding: 1.75rem 2rem 3rem;
            max-width: 1350px;
        }

        /* Top Blue Gradient Header Card */
        .gradient-header-card {
            background: var(--primary-blue-gradient);
            border-radius: 20px;
            padding: 2rem 2.25rem;
            color: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.25);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .gradient-header-card::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .main-content-panel {
            flex: 1;
            min-width: 0;
            height: 100%;
            overflow-y: auto;
            /* only this area scrolls */
            padding: 1.75rem 2rem 3rem;
        }

        .header-card-title {
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
        }

        .header-card-subtitle {
            font-size: 0.95rem;
            opacity: 0.88;
            font-weight: 500;
        }

        /* Metric Pill Badge on Header Card */
        .header-metric-box {
            background: #ffffff;
            color: var(--text-dark);
            border-radius: 16px;
            padding: 0.9rem 1.75rem;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            min-width: 170px;
        }

        .header-metric-value {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--success-green);
            line-height: 1.1;
        }

        .header-metric-label {
            font-size: 0.725rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--success-green);
            margin-top: 0.2rem;
        }

        /* Tabs & Filter Bar */
        .subnav-filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .custom-nav-tabs {
            display: flex;
            gap: 0.5rem;
            background: #ffffff;
            padding: 0.35rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .custom-nav-tab-btn {
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.5rem 1.1rem;
            border-radius: 9px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .custom-nav-tab-btn:hover {
            color: var(--text-dark);
            background-color: #f1f5f9;
        }

        .custom-nav-tab-btn.active {
            background: #ffffff;
            color: var(--primary-blue);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }

        .flash-stack {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1060;
            width: min(420px, calc(100% - 2rem));
        }

        /* Filter Controls */
        .filter-controls-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #ffffff;
            padding: 0.35rem 0.5rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .filter-search-input {
            border: none;
            background: transparent;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 0.4rem 0.6rem;
            outline: none;
            min-width: 170px;
        }

        .filter-date-input {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 8px;
            font-size: 0.825rem;
            padding: 0.35rem 0.6rem;
            outline: none;
            color: #475569;
        }

        .filter-clear-btn {
            border: none;
            background: #f1f5f9;
            color: #64748b;
            border-radius: 8px;
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.2s;
            text-decoration: none;
        }

        .filter-clear-btn:hover {
            background: #e2e8f0;
            color: var(--text-dark);
        }

        /* Data Card Container */
        .data-log-card {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid var(--card-border);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }

        .data-log-header {
            padding: 1.25rem 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f1f5f9;
        }

        .data-log-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        .btn-table-action {
            background-color: var(--primary-blue);
            color: #ffffff;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 9px;
            padding: 0.45rem 1rem;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.2);
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-table-action:hover {
            background-color: var(--primary-blue-hover);
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* Table Aesthetics */
        .table-responsive {
            margin: 0;
        }

        .modern-table {
            width: 100%;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .modern-table th {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #94a3b8;
            padding: 1rem 1.5rem;
            background-color: #fafbfc;
            border-bottom: 1px solid #f1f5f9;
            border-top: none;
        }

        .modern-table td {
            font-size: 0.875rem;
            font-weight: 500;
            color: #334155;
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid #f8fafc;
            vertical-align: middle;
        }

        .modern-table tbody tr:hover td {
            background-color: #fbfcfe;
        }

        .modern-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Status & Count Badges */
        .badge-received {
            background-color: #ecfdf5;
            color: #059669;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
        }

        .badge-issued {
            background-color: #f5f3ff;
            color: #7c3aed;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
        }

        .badge-running-balance {
            font-weight: 800;
            color: #0f172a;
            font-size: 0.925rem;
        }

        .low-stock-alert-pill {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            font-size: 0.725rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Custom Modal Polish */
        .modal-content {
            border-radius: 18px;
            border: none;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            border-bottom: 1px solid #f1f5f9;
            padding: 1.25rem 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #f1f5f9;
            padding: 1rem 1.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 0.6rem 0.85rem;
            font-size: 0.875rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .form-label {
            font-size: 0.825rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.35rem;
        }
    </style>
</head>

<body>

    <!-- Top Navigation Bar -->
    <header class="top-navbar d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('home') }}" class="d-flex align-items-center gap-3 text-decoration-none">
                <div class="d-flex align-items-center justify-content-center text-white rounded-3 shadow-sm"
                    style="width: 40px; height: 40px; background: #1894f2;">
                    <i class="fa-solid fa-plus fs-5"></i>
                </div>
                <div class="d-none d-sm-block">
                    <span class="d-block fw-bold" style="font-size: 1rem; color: #1e293b;">
                        Hospital Inventory Management System
                    </span>
                    <span class="d-block text-uppercase fw-medium"
                        style="font-size: 10px; letter-spacing: .05em; color: #7b838f;">
                        Hospital Management
                    </span>
                </div>
            </a>

            <div class="vr mx-2 text-muted opacity-25 d-none d-md-block" style="height: 28px;"></div>
            <a href="{{ route('home') }}"
                class="hidden sm:inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-[#1894f2] bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 px-3.5 py-2 rounded-xl transition-all shadow-sm active:scale-95">
                <i class="fa-solid fa-arrow-left text-[11px]"></i>
                Back to Home
            </a>

            <div class="vr mx-2 text-muted opacity-25 d-none d-md-block" style="height: 28px;"></div>

            @php
                $isHomeOrDashboard = request()->is('home*') || request()->is('dashboard*') || request()->is('/') || request()->routeIs('home', 'dashboard');
                $isInventoryRoute = request()->is('inventory*') || request()->is('general-inventory*') || request()->routeIs('inventory.*', 'general-inventory.*');
                $isOrdersRoute = request()->is('orders*') || request()->routeIs('orders.*');
                $isPatientsRoute = request()->is('patients*') || request()->routeIs('patients.*');
                $isMasterDataRoute = request()->is('form-types*') || request()->is('units*') || request()->is('suppliers*') || request()->routeIs('form-types.*', 'units.*', 'suppliers.*');

                $showInventory = true;
                $showOrders = true;
                $showPatients = true;

                if (!$isHomeOrDashboard) {
                    if ($isInventoryRoute) {
                        $showOrders = false;
                        $showPatients = false;
                    } elseif ($isOrdersRoute) {
                        $showInventory = false;
                        $showPatients = false;
                    } elseif ($isPatientsRoute) {
                        $showInventory = false;
                        $showOrders = false;
                    } elseif ($isMasterDataRoute) {
                        $showInventory = false;
                        $showOrders = false;
                        $showPatients = false;
                    }
                }
            @endphp

            <!-- Main Navigation Links -->
            <nav class="d-none d-md-flex align-items-center gap-1">
                @if($showInventory)
                <a href="{{ route('general-inventory.index') }}" class="nav-pill-link {{ request()->is('general-inventory*') || request()->is('inventory*') ? 'active' : '' }}">
                    <i class="bi bi-boxes"></i>
                    <span>Inventory</span>
                </a>
                @endif
                @if($showOrders)
                <a href="{{ route('orders.index') }}" class="nav-pill-link {{ request()->is('orders*') ? 'active' : '' }}">
                    <i class="bi bi-cart-check"></i>
                    <span>Orders</span>
                </a>
                @endif
                @if($showPatients)
                <a href="{{ route('patients.index') }}" class="nav-pill-link {{ request()->is('patients*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Patients</span>
                </a>
                @endif

                <!-- Master Data Dropdown -->
                <div class="dropdown">
                    <a href="#" class="nav-pill-link dropdown-toggle {{ $isMasterDataRoute ? 'active' : '' }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-database-gear"></i>
                        <span>Master Data</span>
                    </a>
                    <ul class="dropdown-menu border-0 shadow-lg rounded-4 p-2 mt-2" style="min-width: 220px;">
                        <li>
                            <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2 fw-semibold {{ request()->is('form-types*') || request()->routeIs('form-types.*') ? 'active bg-primary text-white' : 'text-slate-700' }}" href="{{ route('form-types.index') }}">
                                <i class="fa-solid fa-capsules {{ request()->is('form-types*') || request()->routeIs('form-types.*') ? 'text-white' : 'text-primary' }}"></i>
                                <div>
                                    <div style="font-size: 0.85rem; line-height: 1.2;">Form Types</div>
                                    <small class="{{ request()->is('form-types*') || request()->routeIs('form-types.*') ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.7rem;">Medicine dosage forms</small>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1 opacity-25"></li>
                        <li>
                            <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2 fw-semibold {{ request()->is('units*') || request()->routeIs('units.*') ? 'active bg-primary text-white' : 'text-slate-700' }}" href="{{ route('units.index') }}">
                                <i class="fa-solid fa-scale-balanced {{ request()->is('units*') || request()->routeIs('units.*') ? 'text-white' : 'text-primary' }}"></i>
                                <div>
                                    <div style="font-size: 0.85rem; line-height: 1.2;">Units</div>
                                    <small class="{{ request()->is('units*') || request()->routeIs('units.*') ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.7rem;">Measurement units</small>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1 opacity-25"></li>
                        <li>
                            <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2 fw-semibold {{ request()->is('suppliers*') || request()->routeIs('suppliers.*') ? 'active bg-primary text-white' : 'text-slate-700' }}" href="{{ route('suppliers.index') }}">
                                <i class="fa-solid fa-truck-medical {{ request()->is('suppliers*') || request()->routeIs('suppliers.*') ? 'text-white' : 'text-primary' }}"></i>
                                <div>
                                    <div style="font-size: 0.85rem; line-height: 1.2;">Suppliers</div>
                                    <small class="{{ request()->is('suppliers*') || request()->routeIs('suppliers.*') ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.7rem;">Pharmaceutical vendors</small>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>

        <div class="d-flex align-items-center gap-3">
            @auth
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 0.8rem;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="d-none d-lg-block text-start">
                    <div class="fw-bold" style="font-size: 0.825rem; line-height: 1.1;">{{ auth()->user()->name }}</div>
                    <small class="text-muted" style="font-size: 0.7rem;">{{ auth()->user()->role ?? 'Staff' }}</small>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="ms-1">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="flash-stack">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm d-flex align-items-center gap-2 mb-2" role="alert">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div class="fw-semibold">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm d-flex align-items-center gap-2 mb-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
            <div class="fw-semibold">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-warning alert-dismissible fade show rounded-4 border-0 shadow-sm mb-2" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle me-1"></i> Please check the following input errors:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
    </div>

    <!-- Main Layout Frame -->
    <div class="app-body">
        @yield('content')
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Auto-dismiss success alert messages after 3 seconds -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const successAlerts = document.querySelectorAll('.alert-success');
            successAlerts.forEach(function (alert) {
                if (!alert.dataset.dismissScheduled) {
                    alert.dataset.dismissScheduled = 'true';
                    setTimeout(function () {
                        alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-10px)';
                        setTimeout(function () {
                            if (alert.parentNode) {
                                alert.remove();
                            }
                        }, 500);
                    }, 3000);
                }
            });
        });
    </script>

    @stack('scripts')
</body>

</html>