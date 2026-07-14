<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ward Inventory</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background: #f4f6fb;
        }

        /* ===== TOP NAVBAR ===== */
     /* ===== TOP NAVBAR ===== */
       .navbar {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            padding: 16px 32px;
            border-bottom: 1px solid #e5e7eb;
        }

        .navbar__logo-icon {
            width: 26px;
            height: 26px;
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

                .sidebar__search {
    display: flex;
    align-items: center;
    background: #f3f4f9;
    border: none;
    border-radius: 12px;
    padding: 12px 16px;
    gap: 10px;
    margin-bottom: 20px;
}

.sidebar__search svg {
    color: #9ca3af;
    flex-shrink: 0;
}

.sidebar__search input {
    border: none;
    outline: none;
    background: transparent;
    font-size: 14px;
    color: #6b7280;
    width: 100%;
}

.sidebar__search input::placeholder {
    color: #9ca3af;
}

.sidebar__search button {
    border: none;
    background: transparent;
    padding: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
}


        /* ===== PAGE LAYOUT ===== */
        .layout {
            display: flex;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 280px;
            background: #fff;
            padding: 20px 16px;
            min-height: calc(100vh - 64px);
            border-right: 1px solid #e5e7eb;
        }

        .drug-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 12px;
            border-radius: 10px;
            cursor: pointer;
            margin-bottom: 4px;
        }

        .drug-item:hover {
            background: #f3f4f6;
        }

        .drug-item__name {
            font-size: 15px;
            color: #1f2937;
            font-weight: 500;
        }

        .drug-item__qty {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            background: #eef0f4;
            padding: 4px 10px;
            border-radius: 999px;
        }
        .qty--good    { background: #dcfce7; color: #16a34a; }
        .qty--warning { background: #fef3c7; color: #b45309; }
        .qty--danger  { background: #fee2e2; color: #dc2626; }
        .qty--neutral { background: #eef0f4; color: #6b7280; }

        .drug-item--active {
            background: #eef2ff;
        }

        .drug-item--active .drug-item__name {
            color: #1d3fae;
            font-weight: 700;
        }

        
        /* ===== MAIN CONTENT ===== */
        .content {
            flex: 1;
            padding: 30px;
        }

        /* ===== STOCK HEADER (blue bar) ===== */
     .stock-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background:  #1e3a8a;
            border-radius: 14px;
            padding: 28px 32px;
            color: #fff;
            margin-bottom: 20px;
        }

        .stock-header__title {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stock-header__subtitle {
            font-size: 14px;
            opacity: 0.9;
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

        /* ===== SUB TABS ===== */
        .sub-tabs {
            display: flex;
            gap: 10px;
            background: #fff;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
        }

        .pill {
            padding: 10px 20px;
            border-radius: 999px;
            border: none;
            background: transparent;
            color: #6b7280;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }

        .pill--active {
            background: #fff;
            color: #1f2937;
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        }

        /* ===== LOG CARD ===== */
        .log-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .log-card__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .log-card__title {
            font-size: 17px;
            font-weight: 700;
            color: #1f2937;
        }

        .btn-add {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #e11d2e;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }

        /* ===== TABLE ===== */
        .log-table {
            width: 100%;
            border-collapse: collapse;
        }

        .log-table th {
            text-align: left;
            font-size: 11px;
            letter-spacing: 0.4px;
            color: #6b7280;
            text-transform: uppercase;
            padding: 14px 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .log-table td {
            padding: 18px 24px;
            font-size: 14px;
            color: #1f2937;
            border-bottom: 1px solid #f1f2f5;
        }

        .log-table tr:last-child td {
            border-bottom: none;
        }

        .req-no {
            color: #1d3fae;
            font-weight: 600;
        }

        .badge-approved {
            display: inline-block;
            background: #dcfce7;
            color: #16a34a;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 999px;
        }
    </style>
</head>
<body>

    <!-- ===== TOP NAVBAR ===== -->


    <div class="layout">

        <!-- ===== SIDEBAR ===== -->
         
        <div class="sidebar">
            
<div class="sidebar__brand">
        <svg class="navbar__logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M2 12h4l2 8 4-16 2 8h8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <div class="navbar__titles">
            <span class="navbar__title">Injection</span>
            <span class="navbar__subtitle">Ward Inventory(47 & 48)</span>
        </div>
    </div>

    <form action="{{ url('/injA') }}" method="GET" class="sidebar__search">
        <button type="submit">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </button>
        <input type="text" name="search" placeholder="Search medicines..." value="{{ request('search') }}">
    </form>


           
            <div class="drug-item">
                <span class="drug-item__name">Fentanyl</span>
                <span class="drug-item__qty qty--good">45 mcg</span>
            </div>
            <div class="drug-item">
                <span class="drug-item__name">Pethidine</span>
                <span class="drug-item__qty qty--neutral">12 amps</span>
            </div>
            <div class="drug-item">
                <span class="drug-item__name">Ketamine</span>
                <span class="drug-item__qty qty--warning">8 vials</span>
            </div>
            <div class="drug-item">
                <span class="drug-item__name">Midazolam</span>
                <span class="drug-item__qty qty--neutral">24 amps</span>
            </div>
            <div class="drug-item drug-item--active">
                <span class="drug-item__name">Diazepam</span>
                <span class="drug-item__qty qty--warning">18 amps</span>
            </div>
            <div class="drug-item">
                <span class="drug-item__name">Phenobarbital</span>
                <span class="drug-item__qty qty--good">32 vials</span>
            </div>
            <div class="drug-item">
                <span class="drug-item__name">Methadone</span>
                <span class="drug-item__qty qty--warning">150 ml</span>
            </div>
            <div class="drug-item">
                <span class="drug-item__name">Oxycodone</span>
                <span class="drug-item__qty qty--good">42 tabs</span>
            </div>
            <div class="drug-item">
                <span class="drug-item__name">Tramadol</span>
                <span class="drug-item__qty qty--good">60 amps</span>
            </div>
        </div>

        <!-- ===== MAIN CONTENT ===== -->
        <div class="content">

            <!-- Stock header -->
            <div class="stock-header">
                <div>
                    <div class="stock-header__title">Diazepam</div>
                    <div class="stock-header__subtitle">Current Ward Stock Balance</div>
                </div>
                <div class="stock-header__balance">
                    <span class="stock-header__qty">85 tabs</span>
                    <span class="stock-header__label">Available</span>
                </div>
            </div>

            <!-- Sub tabs -->
            <div class="sub-tabs">
                <button class="pill pill--active">Pharmacy Orders (1)</button>
              
            </div>

            <!-- Log card -->
            <div class="log-card">
                <div class="log-card__header">
                    <div class="log-card__title">Pharmacy Requisitions Log</div>
                    <button class="btn-add">+ Add Order</button>
                </div>

                <table class="log-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Req. No.</th>
                            <th>Name of Drug</th>
                            <th>Qty Requested</th>
                            <th>Requested By</th>
                            <th>MS Approval</th>
                            <th>Qty Received</th>
                            <th>Issuing Officer</th>
                            <th>Issuing Date</th>
                            <th>Receiving Officer</th>
                             <th>Receiving date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>05 Jul 2026</td>
                            <td class="req-no">REQ-2026-032</td>
                            <td>100 tabs</td>
                            <td>Nurse Anita K.</td>
                            <td><span class="badge-approved">Approved</span></td>
                            <td>100 tabs</td>
                            <td>Pharm. J. Dilan</td>
                            <td>Nurse Anita K.</td>
                            <td><input type="date" id="Issuing Date" name="issuing_date" class="form-input" value="{{ old('issuing_date', date('Y-m-d')) }}" required></td>
                            <td>Nurse Anita K.</td>
                            <td><input type="date" id="Receiving date" name="receiving_date" class="form-input" value="{{ old('issuing_date', date('Y-m-d')) }}" required></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</body>
</html>