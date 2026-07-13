<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ward Inventory - Oral Countable Inventory Log</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f0f4ff;
            --sidebar-bg: #ffffff;
            --header-bg: #2541e8;
            --header-gradient: linear-gradient(135deg, #1a2ecc 0%, #2541e8 50%, #3d5af1 100%);
            --header-stat-bg: rgba(255, 255, 255, 0.15);
            --header-stat-border: rgba(255, 255, 255, 0.25);
            --card-bg: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-color: #e2e8f0;
            --active-bg: #eef2ff;
            --active-text: #2541e8;
            --active-tab-bg: #1a2ecc;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            background-color: var(--bg-color);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar layout */
        .app-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background-color: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 10;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-header h2 {
            margin: 0;
            font-size: 1.125rem;
            color: #2541e8;
        }

        .medicine-list {
            list-style: none;
            padding: 1rem;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .medicine-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            color: var(--text-secondary);
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .medicine-item:hover {
            background-color: #f1f5f9;
            color: var(--text-primary);
        }

        .medicine-item.active {
            background-color: var(--active-bg);
            color: var(--active-text);
            border-color: #bfdbfe;
        }

        .med-balance-badge {
            background-color: #f1f5f9;
            color: var(--text-secondary);
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-weight: 600;
        }
        
        .medicine-item.active .med-balance-badge {
            background-color: #dbeafe;
            color: var(--active-text);
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 2rem;
            max-width: calc(100vw - 280px);
        }

        .medicine-view {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .medicine-view.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Banner */
        .banner {
            background: var(--header-gradient);
            border-radius: 16px;
            padding: 1.5rem 2rem;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 24px rgba(37, 65, 232, 0.3);
        }

        .banner-left h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 0 0.25rem 0;
        }
        
        .banner-left p {
            margin: 0;
            color: rgba(255,255,255,0.9);
            font-size: 0.875rem;
        }

        .banner-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .banner-stat {
            background-color: var(--header-stat-bg);
            border: 1px solid var(--header-stat-border);
            border-radius: 12px;
            padding: 0.5rem 1.25rem;
            text-align: center;
        }

        .banner-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            display: block;
        }
        .banner-stat-label {
            font-size: 0.625rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: rgba(255,255,255,0.8);
        }

        /* Controls / Tabs */
        .controls-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid var(--border-color);
        }

        .view-tabs {
            display: flex;
            gap: 0.5rem;
            background-color: #f1f5f9;
            padding: 0.25rem;
            border-radius: 9999px;
        }

        .view-tab {
            background: transparent;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text-secondary);
            cursor: pointer;
        }
        
        .view-tab:hover {
            color: var(--text-primary);
        }

        .view-tab.active {
            background-color: var(--card-bg);
            color: var(--text-primary);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Tables */
        .table-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid var(--border-color);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .table-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f8fafc;
        }
        
        .table-title {
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            font-size: 1rem;
        }

        .add-btn {
            background: var(--header-gradient);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: box-shadow 0.2s, transform 0.15s;
            box-shadow: 0 3px 10px rgba(37, 65, 232, 0.35);
        }
        
        .add-btn:hover {
            box-shadow: 0 5px 16px rgba(37, 65, 232, 0.5);
            transform: translateY(-1px);
        }

        .data-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            text-align: left;
        }

        .data-table th {
            color: var(--text-secondary);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background-color: #ffffff;
        }

        .data-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.875rem;
            color: var(--text-primary);
            font-weight: 500;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover td {
            background-color: #f8fafc;
        }

        .table-view-section {
            display: none;
        }
        .table-view-section.active {
            display: block;
        }

        /* Status Pills & Tags */
        .status-pill {
            font-size: 0.75rem;
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            font-weight: 600;
        }
        .status-pill.approved { background-color: #d1fae5; color: #065f46; }
        .status-pill.pending { background-color: #fef3c7; color: #92400e; }

        .status-red { background-color: #fee2e2 !important; color: #991b1b !important; border: 1px solid #fca5a5; }
        .status-yellow { background-color: #fef9c3 !important; color: #854d0e !important; border: 1px solid #fde047; }
        .status-green { background-color: #dcfce7 !important; color: #166534 !important; border: 1px solid #86efac; }

        .req-no {
            font-family: monospace;
            color: var(--active-text);
            background-color: var(--active-bg);
            padding: 0.125rem 0.5rem;
            border-radius: 4px;
        }

        /* ── Sidebar Search ── */
        .sidebar-search-wrap { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color); }
        .sidebar-search-inner { position: relative; display: flex; align-items: center; }
        .sidebar-search-inner .s-icon { position: absolute; left: 0.65rem; width: 14px; height: 14px; color: var(--text-muted); pointer-events: none; flex-shrink: 0; }
        .sidebar-search-input {
            width: 100%; padding: 0.5rem 0.75rem 0.5rem 2.1rem;
            border: 1px solid var(--border-color); border-radius: 8px;
            font-size: 0.8125rem; font-family: 'Inter', sans-serif;
            color: var(--text-primary); background-color: #f8fafc;
            outline: none; transition: border-color 0.2s, box-shadow 0.2s;
        }
        .sidebar-search-input::placeholder { color: var(--text-muted); }
        .sidebar-search-input:focus { border-color: var(--header-bg); box-shadow: 0 0 0 3px rgba(37,65,232,0.12); background-color: #fff; }
        .medicine-item.hidden-med { display: none; }
        .tab-filter-row { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; }
        .filter-controls { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
        .filter-input-wrap { position: relative; display: flex; align-items: center; }
        .filter-input-wrap .s-icon { position: absolute; left: 0.6rem; width: 13px; height: 13px; color: var(--text-muted); pointer-events: none; }
        .filter-input {
            padding: 0.4375rem 0.75rem 0.4375rem 2rem;
            border: 1px solid var(--border-color); border-radius: 8px;
            font-size: 0.8125rem; font-family: 'Inter', sans-serif;
            color: var(--text-primary); background-color: #f8fafc;
            outline: none; transition: border-color 0.2s, box-shadow 0.2s;
        }
        .filter-input::placeholder { color: var(--text-muted); }
        .filter-input:focus { border-color: var(--header-bg); box-shadow: 0 0 0 3px rgba(37,65,232,0.12); background-color: #fff; }
        .clear-btn { padding: 0.4375rem 0.875rem; background-color: #f1f5f9; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.8125rem; font-weight: 600; font-family: 'Inter', sans-serif; color: var(--text-secondary); cursor: pointer; transition: all 0.2s; white-space: nowrap; }
        .clear-btn:hover { background-color: var(--active-bg); color: var(--active-text); border-color: #bfdbfe; }

    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <svg style="width: 28px; height: 28px; color: #2541e8; flex-shrink: 0;" fill="none" viewBox="0 0 32 32" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 16h5l3-9 6 18 4-12 2 3h8"></path>
                </svg>
                <div style="display: flex; flex-direction: column;">
                    <h2>Ward Inventory</h2>
                    <span style="font-size: 0.75rem; color: var(--text-secondary); font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase;">Oral-Countable</span>
                </div>
            </div>
            <div class="sidebar-search-wrap">
                <div class="sidebar-search-inner">
                    <svg class="s-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    <input type="text" class="sidebar-search-input" placeholder="Search medicines..." oninput="filterMedicines(this.value)">
                </div>
            </div>
            <ul class="medicine-list" id="medicine-nav">
                @foreach($medicines as $index => $medicine)
                    @php
                        $statusClass = '';
                        if ($medicine['numeric_balance'] < $medicine['min_level']) $statusClass = 'status-red';
                        elseif ($medicine['numeric_balance'] <= $medicine['warning_limit']) $statusClass = 'status-yellow';
                        else $statusClass = 'status-green';
                    @endphp
                    <li class="medicine-item {{ $index === 0 ? 'active' : '' }}" onclick="switchMedicine({{ $index }})" data-index="{{ $index }}">
                        <span>{{ $medicine['name'] }}</span>
                        <span class="med-balance-badge {{ $statusClass }}">{{ $medicine['balance'] }}</span>
                    </li>
                @endforeach
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            @foreach($medicines as $index => $medicine)
                <div id="med-view-{{ $index }}" class="medicine-view {{ $index === 0 ? 'active' : '' }}">
                    
                    <!-- Banner -->
                    <div class="banner">
                        <div class="banner-left">
                            <h1>{{ $medicine['name'] }}</h1>
                            <p>Current Ward Stock Balance</p>
                        </div>
                        <div class="banner-right">
                            @php
                                $statusClass = '';
                                if ($medicine['numeric_balance'] < $medicine['min_level']) $statusClass = 'status-red';
                                elseif ($medicine['numeric_balance'] <= $medicine['warning_limit']) $statusClass = 'status-yellow';
                                else $statusClass = 'status-green';
                            @endphp
                            <div class="banner-stat {{ $statusClass }}" style="border-color: transparent;">
                                <span class="banner-stat-value" style="color: inherit;">{{ $medicine['balance'] }}</span>
                                <span class="banner-stat-label" style="color: inherit;">AVAILABLE</span>
                            </div>
                        </div>
                    </div>

                    <!-- Controls / Tabs -->
                    <div class="controls-card">
                        <div class="tab-filter-row">
                            <div class="view-tabs">
                                <button class="view-tab active" onclick="switchTab({{ $index }}, 'pharmacy')" id="tab-btn-pharmacy-{{ $index }}">
                                    Pharmacy Orders ({{ count($medicine['orders']) }})
                                </button>
                                <button class="view-tab" onclick="switchTab({{ $index }}, 'patient')" id="tab-btn-patient-{{ $index }}">
                                    Patient Administrations ({{ count($medicine['dispensations']) }})
                                </button>
                            </div>
                            <div class="filter-controls">
                                <div class="filter-input-wrap">
                                    <svg class="s-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                                    <input type="text" class="filter-input" id="table-search-{{ $index }}" placeholder="Search records..." oninput="filterTable({{ $index }})">
                                </div>
                                <div class="filter-input-wrap">
                                    <svg class="s-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    <input type="date" class="filter-input" id="date-filter-{{ $index }}" oninput="filterTable({{ $index }})">
                                </div>
                                <button class="clear-btn" onclick="clearFilters({{ $index }})">✕ Clear</button>
                            </div>
                        </div>
                    </div>

                    <!-- Pharmacy Orders Table -->
                    <div id="table-pharmacy-{{ $index }}" class="table-view-section active">
                        <div class="table-card">
                            <div class="table-header">
                                <h3 class="table-title">Pharmacy Requisitions Log</h3>
                                <button class="add-btn">
                                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Add Order
                                </button>
                            </div>
                            <div style="overflow-x: auto;">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Req. No.</th>
                                            <th>Qty Requested</th>
                                            <th>Requested By</th>
                                            <th>MS Approval</th>
                                            <th>Qty Received</th>
                                            <th>Issuing Officer</th>
                                            <th>Receiving Officer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($medicine['orders'] as $order)
                                            <tr>
                                                <td>{{ $order['date'] }}</td>
                                                <td><span class="req-no">{{ $order['req_no'] }}</span></td>
                                                <td>{{ $order['qty_requested'] }}</td>
                                                <td>{{ $order['req_officer'] }}</td>
                                                <td>
                                                    <span class="status-pill {{ strtolower($order['ms_approval']) === 'approved' ? 'approved' : 'pending' }}">
                                                        {{ $order['ms_approval'] }}
                                                    </span>
                                                </td>
                                                <td>{{ $order['qty_received'] }}</td>
                                                <td>{{ $order['issue_officer'] }}</td>
                                                <td>{{ $order['receive_officer'] }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="8" style="text-align:center; padding: 2rem;">No pharmacy orders found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Patient Administrations Table -->
                    <div id="table-patient-{{ $index }}" class="table-view-section">
                        <div class="table-card">
                            <div class="table-header">
                                <h3 class="table-title">Patient Dispensations & Admin Log</h3>
                                <button class="add-btn">
                                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Add Administration
                                </button>
                            </div>
                            <div style="overflow-x: auto;">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>B.H.T No.</th>
                                            <th>Qty Given</th>
                                            <th>Balance</th>
                                            <th>Sister Initials</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($medicine['dispensations'] as $disp)
                                            <tr>
                                                <td>{{ $disp['date'] }}</td>
                                                <td><span class="req-no">{{ $disp['bht_no'] }}</span></td>
                                                <td>{{ $disp['qty_given'] }}</td>
                                                <td style="font-weight:700; color:#2541e8;">{{ $disp['balance'] }}</td>
                                                <td>{{ $disp['sister_initials'] }}</td>
                                                <td style="color:var(--text-secondary);">{{ $disp['remark'] }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" style="text-align:center; padding: 2rem;">No patient administrations logged yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            @endforeach
        </main>
    </div>

    <script>
        function filterMedicines(query) {
            const q = query.toLowerCase().trim();
            document.querySelectorAll('.medicine-item').forEach(item => {
                const name = item.querySelector('span').textContent.toLowerCase();
                item.classList.toggle('hidden-med', q !== '' && !name.includes(q));
            });
        }
        function switchMedicine(index) {
            document.querySelectorAll('.medicine-item').forEach(el => el.classList.remove('active'));
            document.querySelector(`.medicine-item[data-index="${index}"]`).classList.add('active');
            document.querySelectorAll('.medicine-view').forEach(el => el.classList.remove('active'));
            document.getElementById(`med-view-${index}`).classList.add('active');
        }
        function switchTab(medicineIndex, tabName) {
            document.getElementById(`tab-btn-pharmacy-${medicineIndex}`).classList.remove('active');
            document.getElementById(`tab-btn-patient-${medicineIndex}`).classList.remove('active');
            document.getElementById(`tab-btn-${tabName}-${medicineIndex}`).classList.add('active');
            document.getElementById(`table-pharmacy-${medicineIndex}`).classList.remove('active');
            document.getElementById(`table-patient-${medicineIndex}`).classList.remove('active');
            document.getElementById(`table-${tabName}-${medicineIndex}`).classList.add('active');
            filterTable(medicineIndex);
        }
        function filterTable(medicineIndex) {
            const searchVal = (document.getElementById(`table-search-${medicineIndex}`)?.value || '').toLowerCase().trim();
            const dateVal   = (document.getElementById(`date-filter-${medicineIndex}`)?.value || '').trim();
            ['pharmacy', 'patient'].forEach(tab => {
                const section = document.getElementById(`table-${tab}-${medicineIndex}`);
                if (!section) return;
                const tbody = section.querySelector('tbody');
                if (!tbody) return;
                let anyVisible = false;
                tbody.querySelectorAll('tr').forEach(row => {
                    if (row.querySelector('td[colspan]')) { row.style.display = 'none'; return; }
                    const rowText  = row.textContent.toLowerCase();
                    const dateCell = row.querySelector('td:first-child');
                    const rowDate  = dateCell ? dateCell.textContent.trim() : '';
                    const visible  = (searchVal === '' || rowText.includes(searchVal)) && (dateVal === '' || rowDate === dateVal);
                    row.style.display = visible ? '' : 'none';
                    if (visible) anyVisible = true;
                });
                let noRow = tbody.querySelector('.js-no-results');
                if (!anyVisible) {
                    if (!noRow) { noRow = document.createElement('tr'); noRow.className = 'js-no-results'; const cols = section.querySelectorAll('thead th').length || 6; noRow.innerHTML = `<td colspan="${cols}" style="text-align:center;padding:2rem;color:var(--text-muted);font-style:italic;">No records match your search.</td>`; tbody.appendChild(noRow); }
                    noRow.style.display = '';
                } else if (noRow) { noRow.style.display = 'none'; }
            });
        }
        function clearFilters(medicineIndex) {
            const s = document.getElementById(`table-search-${medicineIndex}`);
            const d = document.getElementById(`date-filter-${medicineIndex}`);
            if (s) s.value = ''; if (d) d.value = '';
            filterTable(medicineIndex);
        }
    </script>
</body>
</html>