<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ward Inventory System') - Ward 48</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --bs-font-sans-serif: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --primary-blue: #2563eb;
            --primary-blue-hover: #1d4ed8;
            --primary-blue-gradient: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%);
            --bg-light: #f8fafc;
            --card-bg: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --card-border: #edf2f7;
            --active-item-bg: #eff6ff;
            --badge-warning-bg: #fef3c7;
            --badge-warning-text: #d97706;
            --badge-success-bg: #dcfce7;
            --badge-success-text: #15803d;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: var(--bs-font-sans-serif);
            background-color: var(--bg-light);
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
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 1.5rem;
            position: relative;
            flex: 0 0 auto;
            top: 0;
            z-index: 1030;
            backdrop-filter: blur(8px);
        }

        .back-home-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            padding: 0.5rem 0.875rem;
            border-radius: 12px;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .back-home-link:hover {
            color: #1894f2;
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .back-home-link:active {
            transform: scale(0.95);
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

        /* App Container */
        .app-container {
            display: flex;
            flex: 1;
            min-height: 0;
            /* required so children can scroll */
            width: 100%;
        }

        /* Left Sidebar Panel */
        .sidebar {
            width: 330px;
            min-width: 330px;
            max-width: 330px;
            height: 100%;
            background-color: #ffffff;
            border-right: 1px solid var(--border-color);
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            overflow: hidden;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 0.5rem;
        }

        .sidebar-header i {
            color: var(--primary-blue);
            font-size: 22px;
        }

        .sidebar-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1.2;
            letter-spacing: -0.01em;
        }

        .sidebar-subtitle {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .search-box {
            position: relative;
            padding: 0 0.5rem;
        }

        .search-box input {
            width: 100%;
            padding: 0.6rem 0.85rem 0.6rem 2.5rem;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 500;
            background-color: #f8fafc;
            outline: none;
        }

        .search-box input:focus {
            background-color: #ffffff;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .search-box i {
            position: absolute;
            left: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .btn-add-primary {
            margin: 0 0.5rem;
            background-color: var(--primary-blue);
            color: #ffffff;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.28);
        }

        .btn-add-primary:hover {
            background-color: var(--primary-blue-hover);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35);
        }

        .medicine-list {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
            overflow-y: auto;
            flex: 1;
            padding: 0 0.5rem 1rem;
        }

        .medicine-card {
            background: #ffffff;
            border: 1px solid transparent;
            border-radius: 12px;
            padding: 0.85rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            transition: all 0.18s ease;
        }

        .medicine-card.active {
            background-color: var(--active-item-bg);
            border: 1px solid #bfdbfe;
            border-left: 4px solid var(--primary-blue);
            box-shadow: none;
        }

        .medicine-card:hover:not(.active) {
            background-color: #f1f5f9;
        }

        .medicine-name {
            font-size: 0.925rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: #0f172a;
            text-transform: uppercase;
        }

        .medicine-card.active .medicine-name {
            color: #1e40af;
        }

        .medicine-badge {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.3rem 0.65rem;
            border-radius: 8px;
        }

        .medicine-card.active .medicine-badge {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .medicine-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 6px;
            color: #64748b;
            font-size: 0.85rem;
            opacity: 0.4;
            transition: opacity 0.2s;
        }

        .medicine-card:hover .medicine-actions,
        .medicine-card.active .medicine-actions {
            opacity: 1;
        }

        .medicine-actions i:hover {
            color: var(--primary-blue);
        }

        /* Main Content Layout */
        .main-content {
            flex: 1;
            min-width: 0;
            height: 100%;
            overflow-y: auto;
            /* only this area scrolls */
            padding: 1.75rem 2rem 3rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        /* Hero Stock Banner */
        .hero-banner {
            background: var(--primary-blue-gradient);
            border-radius: 20px;
            padding: 2rem 2.25rem;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.25);
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        .hero-banner::before {
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

        .hero-banner-title {
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
        }

        .hero-banner-subtitle {
            font-size: 0.95rem;
            opacity: 0.88;
            font-weight: 500;
        }

        .hero-stock-pill {
            background-color: #ffffff;
            color: var(--text-dark);
            padding: 0.9rem 1.75rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            min-width: 170px;
        }

        .hero-stock-value {
            font-size: 1.65rem;
            font-weight: 800;
            color: #16a34a;
            line-height: 1.1;
        }

        .hero-stock-label {
            font-size: 0.725rem;
            font-weight: 800;
            color: #16a34a;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 0.2rem;
        }

        /* Filter Controls */
        .filter-section {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 0.35rem 0.5rem;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            flex-shrink: 0;
        }

        .orders-count-tab {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary-blue);
            background: #ffffff;
            padding: 0.5rem 1.1rem;
            border-radius: 9px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        }

        .filter-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-input {
            padding: 0.35rem 0.6rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.825rem;
            font-weight: 500;
            outline: none;
            background: #f8fafc;
            color: #475569;
        }

        .filter-input:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .btn-clear-filter {
            background: #f1f5f9;
            border: none;
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-clear-filter:hover {
            background-color: #e2e8f0;
            color: var(--text-dark);
        }

        /* Table Card Section */
        .table-card {
            background-color: #ffffff;
            border-radius: 18px;
            border: 1px solid var(--card-border);
            padding: 0;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            flex-shrink: 0;
        }

        .table-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.75rem;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 0;
        }

        .table-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        .custom-table {
            width: 100%;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
            text-align: left;
        }

        .custom-table th {
            padding: 1rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            background-color: #fafbfc;
            border-bottom: 1px solid #f1f5f9;
            border-top: none;
        }

        .custom-table td {
            padding: 1.1rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #334155;
            border-bottom: 1px solid #f8fafc;
            vertical-align: middle;
        }

        .custom-table tbody tr:hover td {
            background-color: #fbfcfe;
        }

        .custom-table tbody tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-badge.pending {
            background-color: var(--badge-warning-bg);
            color: var(--badge-warning-text);
        }

        .status-badge.approved,
        .status-badge.completed {
            background-color: var(--badge-success-bg);
            color: var(--badge-success-text);
        }

        .status-badge.rejected {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .status-select {
            display: inline-block;
            padding: 6px 26px 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            border: 1px solid transparent;
            outline: none;
            cursor: pointer;
            transition: all 0.2s ease;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23475569'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 12px;
        }

        .status-select.pending {
            background-color: #fef3c7;
            color: #d97706;
            border-color: #fde68a;
        }

        .status-select.completed {
            background-color: #dcfce7;
            color: #15803d;
            border-color: #bbf7d0;
        }

        .status-select.approved {
            background-color: #e0f2fe;
            color: #0369a1;
            border-color: #bae6fd;
        }

        .status-select.rejected {
            background-color: #fee2e2;
            color: #b91c1c;
            border-color: #fca5a5;
        }

        .status-select:hover {
            filter: brightness(0.96);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
        }

        /* Flash Messages */
        .flash-stack {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1060;
            width: min(420px, calc(100% - 2rem));
        }

        /* Modal Styles (custom overlay, kept from original) */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: #ffffff;
            border-radius: 18px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: modalFadeIn 0.25s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .modal-overlay .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-overlay .modal-header h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        .btn-close-modal {
            background: none;
            border: none;
            font-size: 18px;
            color: var(--text-muted);
            cursor: pointer;
        }

        .btn-close-modal:hover {
            color: var(--text-dark);
        }

        .modal-overlay .modal-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .form-group label {
            font-size: 0.825rem;
            font-weight: 600;
            color: #475569;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 0.6rem 0.85rem;
            font-size: 0.875rem;
            width: 100%;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .form-row {
            display: flex;
            gap: 12px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .modal-overlay .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background-color: #ffffff;
        }
    </style>
    @yield('styles')
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

            @if(Route::has('home'))
            <a href="{{ route('home') }}" class="back-home-link d-none d-sm-inline-flex">
                <i class="fa-solid fa-arrow-left" style="font-size: 11px;"></i>
                Back to Home
            </a>
            <div class="vr mx-2 text-muted opacity-25 d-none d-md-block" style="height: 28px;"></div>
            @endif

            <!-- Navigation Links -->
            <nav class="d-none d-md-flex align-items-center gap-1">
                <a href="{{ route('injections.index') }}" class="nav-pill-link {{ request()->routeIs('injections.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-syringe"></i>
                    <span>Injections</span>
                </a>
                <a href="{{ route('injection-antibiotics.index') }}" class="nav-pill-link {{ request()->routeIs('injection-antibiotics.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-capsules"></i>
                    <span>Injection Antibiotics</span>
                </a>
                <a href="{{ route('ms.index') }}" class="nav-pill-link {{ request()->routeIs('ms.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>MS Approval Page</span>
                </a>
            </nav>
        </div>

        <!-- User / Logout -->
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
                @if(Route::has('logout'))
                <form method="POST" action="{{ route('logout') }}" class="ms-1">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
                @endif
            </div>
            @endauth

            @guest
            @if(Route::has('login'))
            <a href="{{ route('login') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </a>
            @endif
            @endguest
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
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
    </div>

    <!-- Main Layout Frame -->
    <div class="app-container">
        @yield('content')
    </div>

    <!-- Modals Stack -->
    @yield('modals')

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Close on clicking outside modal
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.classList.remove('active');
            }
        });

        // Auto-dismiss success alert messages after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successAlerts = document.querySelectorAll('.alert-success');
            successAlerts.forEach(function(alert) {
                if (!alert.dataset.dismissScheduled) {
                    alert.dataset.dismissScheduled = 'true';
                    setTimeout(function() {
                        alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-10px)';
                        setTimeout(function() {
                            if (alert.parentNode) {
                                alert.remove();
                            }
                        }, 500);
                    }, 3000);
                }
            });
        });
    </script>

    @yield('scripts')
    @stack('scripts')
</body>

</html>