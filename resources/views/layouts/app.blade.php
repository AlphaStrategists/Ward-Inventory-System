<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ward Inventory System')</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-blue: #2563eb;
            --primary-blue-hover: #1d4ed8;
            --bg-light: #f1f5f9;
            --card-bg: #ffffff;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --badge-warning-bg: #fef3c7;
            --badge-warning-text: #d97706;
            --badge-success-bg: #dcfce7;
            --badge-success-text: #15803d;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Navbar */
        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .nav-link.active, .nav-link:hover {
            color: var(--primary-blue);
            background-color: #eff6ff;
        }

        /* App Container */
        .app-container {
            display: flex;
            flex: 1;
        }

        /* Left Sidebar Panel */
        .sidebar {
            width: 300px;
            background-color: #ffffff;
            border-right: 1px solid var(--border-color);
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header i {
            color: var(--primary-blue);
            font-size: 22px;
        }

        .sidebar-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.2;
        }

        .sidebar-subtitle {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 12px 10px 36px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 13px;
            background-color: #f8fafc;
            outline: none;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 13px;
        }

        .btn-add-primary {
            background-color: var(--primary-blue);
            color: #ffffff;
            border: none;
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: background-color 0.2s ease;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }

        .btn-add-primary:hover {
            background-color: var(--primary-blue-hover);
        }

        .medicine-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            overflow-y: auto;
        }

        .medicine-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
        }

        .medicine-card.active {
            border-color: var(--primary-blue);
            background-color: #f0f7ff;
            box-shadow: 0 0 0 1px var(--primary-blue);
        }

        .medicine-card:hover:not(.active) {
            background-color: #f8fafc;
        }

        .medicine-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-dark);
            text-transform: uppercase;
        }

        .medicine-badge {
            background-color: #e2e8f0;
            color: var(--text-dark);
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .medicine-actions {
            display: flex;
            gap: 8px;
            margin-top: 6px;
            color: #94a3b8;
            font-size: 12px;
        }

        .medicine-actions i:hover {
            color: var(--primary-blue);
        }

        /* Main Content Layout */
        .main-content {
            flex: 1;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Hero Stock Banner */
        .hero-banner {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border-radius: 16px;
            padding: 28px 32px;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.15);
        }

        .hero-banner-title {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .hero-banner-subtitle {
            font-size: 13px;
            opacity: 0.9;
            margin-top: 4px;
            font-weight: 500;
        }

        .hero-stock-pill {
            background-color: #ffffff;
            color: #1e293b;
            padding: 16px 24px;
            border-radius: 14px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .hero-stock-value {
            font-size: 24px;
            font-weight: 800;
            color: #16a34a;
            line-height: 1;
        }

        .hero-stock-label {
            font-size: 11px;
            font-weight: 700;
            color: #16a34a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        /* Filter Controls */
        .filter-section {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 14px 20px;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .orders-count-tab {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
            background: #f1f5f9;
            padding: 6px 14px;
            border-radius: 20px;
        }

        .filter-controls {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .filter-input {
            padding: 8px 12px 8px 32px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            background: #ffffff;
        }

        .btn-clear-filter {
            background: none;
            border: 1px solid var(--border-color);
            padding: 8px 14px;
            border-radius: 8px;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-clear-filter:hover {
            background-color: #f8fafc;
            color: var(--text-dark);
        }

        /* Table Card Section */
        .table-card {
            background-color: #ffffff;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .table-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .table-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .custom-table th {
            padding: 12px 14px;
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
        }

        .custom-table td {
            padding: 16px 14px;
            font-size: 13px;
            color: var(--text-dark);
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .custom-table tbody tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-badge.pending {
            background-color: var(--badge-warning-bg);
            color: var(--badge-warning-text);
        }

        .status-badge.approved, .status-badge.completed {
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.06);
        }

        /* Alerts */
        .alert-success {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Modal Styles */
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
            border-radius: 16px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: modalFadeIn 0.25s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .btn-close-modal {
            background: none;
            border: none;
            font-size: 18px;
            color: var(--text-muted);
            cursor: pointer;
        }

        .modal-body {
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
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

        .form-row {
            display: flex;
            gap: 12px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background-color: #f8fafc;
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Navigation Header -->
    <nav class="navbar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fa-solid fa-notes-medical" style="color: var(--primary-blue); font-size: 24px;"></i>
            <span style="font-weight: 800; font-size: 18px; color: var(--text-dark);">Ward 48 Inventory Management</span>
        </div>
        <div class="nav-links">
            <a href="{{ route('injections.index') }}" class="nav-link {{ request()->routeIs('injections.index') ? 'active' : '' }}">
                <i class="fa-solid fa-syringe" style="margin-right: 6px;"></i> Injections
            </a>
            <a href="{{ route('injection-antibiotics.index') }}" class="nav-link {{ request()->routeIs('injection-antibiotics.index') ? 'active' : '' }}">
                <i class="fa-solid fa-capsules" style="margin-right: 6px;"></i> Injection Antibiotics
            </a>
            <a href="{{ route('ms.index') }}" class="nav-link {{ request()->routeIs('ms.index') ? 'active' : '' }}">
                <i class="fa-solid fa-user-shield" style="margin-right: 6px;"></i> MS Approval Page
            </a>
        </div>
    </nav>

    <!-- Flash messages -->
    @if(session('success'))
        <div style="padding: 16px 24px 0 24px;">
            <div class="alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div style="padding: 16px 24px 0 24px;">
            <div class="alert-success" style="background-color: #fee2e2; color: #b91c1c; border-color: #fecaca;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <ul style="list-style: none;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="app-container">
        @yield('content')
    </div>

    <!-- Modals Stack -->
    @yield('modals')

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Close on clicking outside modal
        window.addEventListener('click', function (event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.classList.remove('active');
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
