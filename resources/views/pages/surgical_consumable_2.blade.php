<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ward Inventory - Surgical Products 2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Load modern typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ================= BASE / LAYOUT ================= */
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f4f6fb;
            color: #1e293b;
            display: flex;
            min-height: 100vh;
        }

        /* ================= SIDEBAR ================= */
        .sidebar {
            width: 300px;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        .sidebar-header-box {
            padding: 24px;
            border-bottom: 1px solid #f1f5f9;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            background: #ffffff;
            color: rgba(0,0,24,24);;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        .logo-icon svg {
            width: 22px;
            height: 22px;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-title {
            font-size: 18px;
            font-weight: 800;
            color: #1e3a8a;
            line-height: 1.1;
        }

        .logo-subtitle {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 2px;
            margin-top: 2px;
            text-transform: uppercase;
        }

        .sidebar-search-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .sidebar-search-box input {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px 10px 38px;
            font-size: 14px;
            color: #334155;
            outline: none;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .sidebar-search-box input:focus {
            border-color: #2563eb;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .sidebar-search-box svg {
            position: absolute;
            left: 14px;
            color: #94a3b8;
        }

        .sidebar-list {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
        }

        .medicine-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
            border: 1px solid transparent;
        }

        .medicine-item:hover {
            background: #f8fafc;
        }

        .medicine-name {
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            transition: color 0.2s ease;
        }

        .medicine-item.active {
            background: #eff6ff;
            border-color: rgba(37, 99, 235, 0.1);
        }

        .medicine-item.active .medicine-name {
            color: #1e40af;
            font-weight: 700;
        }

        /* ================= BADGES ================= */
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge.red {
            background: #fee2e2;
            color: #ef4444;
        }

        .badge.green {
            background: #dcfce7;
            color: #22c55e;
        }

        .badge.orange {
            background: #ffedd5;
            color: #ea580c;
        }

        .badge.yellow {
            background: #fef9c3;
            color: #ca8a04;
        }

        /* Active selection overrides badge styling */
        .medicine-item.active .badge {
            background: #fef9c3;
            color: #ca8a04;
        }

        /* ================= MAIN CONTENT ================= */
        .main-content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* ================= HEADER CARD ================= */
        .header-card {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            padding: 32px 40px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.15);
        }

        .header-info h2 {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.5px;
        }

        .header-info p {
            margin: 6px 0 0 0;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
        }

        .header-badge {
            background: #fef9c3;
            color: #854d0e;
            padding: 14px 24px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            min-width: 130px;
        }

        .badge-value {
            font-size: 24px;
            font-weight: 800;
            line-height: 1.1;
        }

        .badge-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            margin-top: 3px;
            opacity: 0.9;
        }

        /* ================= CONTROLS BAR ================= */
        .controls-bar {
            background: #ffffff;
            border-radius: 16px;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
            gap: 20px;
            flex-wrap: wrap;
        }

        .tabs-container {
            display: flex;
            gap: 4px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 12px;
        }

        .tab-btn {
            border: none;
            background: transparent;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .tab-btn.active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .filters-container {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .search-wrapper, .date-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-wrapper input, .date-wrapper input {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px 10px 38px;
            font-size: 14px;
            color: #334155;
            outline: none;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .date-wrapper input {
            padding: 9px 14px;
            cursor: pointer;
            min-width: 150px;
        }

        .search-wrapper svg {
            position: absolute;
            left: 14px;
            color: #94a3b8;
        }

        .clear-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .clear-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        /* ================= TABLE CARD ================= */
        .table-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .table-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .table-card-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        .btn-primary {
            background: #2563eb;
            color: #ffffff;
            border: none;
            padding: 11px 22px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .table-wrapper {
            overflow-x: auto;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            padding: 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #94a3b8;
            border-bottom: 1px solid #f1f5f9;
            letter-spacing: 0.5px;
        }

        td {
            padding: 16px;
            font-size: 14px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        tr:hover td {
            background-color: #f8fafc;
        }

        .signature-text {
            font-weight: 600;
            color: #475569;
        }

        .balance-val {
            font-weight: 700;
            color: #1d4ed8;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
            font-size: 14px;
            font-style: italic;
        }

        /* ================= MODAL DIALOG ================= */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            background: #ffffff;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transform: translateY(20px);
            transition: transform 0.3s ease;
            overflow: hidden;
        }

        .modal-overlay.active .modal-content {
            transform: translateY(0);
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        .close-modal-btn {
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .close-modal-btn:hover {
            background: #f1f5f9;
            color: #475569;
        }

        .form-group {
            padding: 12px 24px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
        }

        .form-group input, .form-group select {
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            font-family: inherit;
            transition: all 0.2s ease;
            background: #ffffff;
        }

        .form-group input:focus, .form-group select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .qty-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .qty-input-wrapper input {
            width: 100%;
            padding-right: 70px;
        }

        .qty-unit-label {
            position: absolute;
            right: 14px;
            font-size: 13px;
            font-weight: 600;
            color: #94a3b8;
        }

        .modal-footer {
            padding: 20px 24px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: #f8fafc;
        }

        .modal-footer .btn-secondary {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .modal-footer .btn-secondary:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        /* ================= RESPONSIVE ================= */
        @media(max-width: 1024px) {
            body {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #e2e8f0;
            }
            .main-content {
                padding: 24px;
            }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-header-box">
            <div class="logo-container">
                <div class="logo-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                    </svg>
                </div>
                <div class="logo-text">
                    <span class="logo-title">Ward Inventory</span>
                    <span class="logo-subtitle">SURGICAL 2</span>
                </div>
            </div>

            <div class="sidebar-search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="sidebarSearch" placeholder="Search products..." oninput="filterSidebar()">
            </div>
        </div>

        <div class="sidebar-list" id="medicineList">
            <!-- Dynamic Inject -->
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- HEADER CARD -->
        <div class="header-card">
            <div class="header-info">
                <h2 id="productTitle">Loading...</h2>
                <p>Current Ward Stock Balance</p>
            </div>
            <div class="header-badge">
                <div class="badge-value" id="headerBalanceValue">-</div>
                <div class="badge-label">AVAILABLE</div>
            </div>
        </div>

        <!-- CONTROLS BAR -->
        <div class="controls-bar">
            <div class="tabs-container">
                <button class="tab-btn active" id="tabAdmin" onclick="switchTab('standard')">Standard Requisitions (0)</button>
                <button class="tab-btn" id="tabBorrowed" onclick="switchTab('borrowed')">Borrowed Stock (0)</button>
            </div>

            <div class="filters-container">
                <div class="search-wrapper">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="logSearch" placeholder="Search records..." oninput="filterLogs()">
                </div>

                <div class="date-wrapper">
                    <input type="date" id="logDateFilter" onchange="filterLogs()">
                </div>

                <button class="clear-btn" id="clearFilterBtn" onclick="clearFilters()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Clear
                </button>
            </div>
        </div>

        <!-- TABLE CARD -->
        <div class="table-card">
            <div class="table-card-header">
                <h3 id="tableTitle">Requisitions Log</h3>
                <button class="btn-primary" id="addRecordBtn" onclick="openModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span id="addBtnLabel">Add Record</span>
                </button>
            </div>

            <div class="table-wrapper">
                <table id="logTable">
                    <thead id="tableHeader">
                        <tr>
                            <th>DATE</th>
                            <th>BHT</th>
                            <th>QUANTITY</th>
                            <th>BALANCE</th>
                            <th>SIGNATURE IN CHARGE</th>
                            <th>NOTE</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Dynamic Inject -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ADD RECORD MODAL -->
    <div id="addRecordModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add Log</h3>
                <button class="close-modal-btn" onclick="closeModal()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <form id="recordForm" onsubmit="saveRecord(event)">
                <div class="form-group">
                    <label for="recordDate">Date</label>
                    <input type="date" id="recordDate" required>
                </div>
                <div class="form-group">
                    <label for="recordBht">BHT Number</label>
                    <input type="text" id="recordBht" placeholder="e.g. BHT-48291" required>
                </div>
                <div class="form-group">
                    <label for="recordQty">Quantity Issued</label>
                    <div class="qty-input-wrapper">
                        <input type="number" id="recordQty" min="1" placeholder="e.g. 10" required>
                        <span id="qtyUnitLabel" class="qty-unit-label">pairs</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="recordSig">Signature in Charge</label>
                    <select id="recordSig" required>
                        <option value="" disabled selected>Select Staff</option>
                        <option value="Sr. M. Dlamini">Sr. M. Dlamini</option>
                        <option value="Sr. Zulu">Sr. Zulu</option>
                        <option value="Sr. Mokoena">Sr. Mokoena</option>
                        <option value="Sr. Naidoo">Sr. Naidoo</option>
                        <option value="Dr. K. Nkosi">Dr. K. Nkosi</option>
                        <option value="Dr. Adams">Dr. Adams</option>
                        <option value="Dr. Khan">Dr. Khan</option>
                        <option value="Dr. Smith">Dr. Smith</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="recordNote">Note</label>
                    <input type="text" id="recordNote" placeholder="e.g. Post-op closure" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Record</button>
                </div>
            </form>
        </div>
    </div>

    <!-- BORROW RECORD MODAL -->
    <div id="borrowRecordModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="borrowModalTitle">Borrow Stock</h3>
                <button class="close-modal-btn" onclick="closeBorrowModal()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <form id="borrowForm" onsubmit="saveBorrowRecord(event)">
                <div class="form-group">
                    <label for="borrowDate">Date</label>
                    <input type="date" id="borrowDate" required>
                </div>
                <div class="form-group">
                    <label for="borrowQty">Quantity to Borrow</label>
                    <div class="qty-input-wrapper">
                        <input type="number" id="borrowQty" min="1" placeholder="e.g. 10" required>
                        <span id="borrowQtyUnitLabel" class="qty-unit-label">pairs</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="borrowStaff">Staff Member Name</label>
                    <select id="borrowStaff" required>
                        <option value="" disabled selected>Select Staff</option>
                        <option value="Sr. M. Dlamini">Sr. M. Dlamini</option>
                        <option value="Sr. Zulu">Sr. Zulu</option>
                        <option value="Sr. Mokoena">Sr. Mokoena</option>
                        <option value="Sr. Naidoo">Sr. Naidoo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="borrowSig">Signature/Confirmation</label>
                    <select id="borrowSig" required>
                        <option value="" disabled selected>Select Confirmer</option>
                        <option value="Dr. K. Nkosi">Dr. K. Nkosi</option>
                        <option value="Dr. Adams">Dr. Adams</option>
                        <option value="Dr. Khan">Dr. Khan</option>
                        <option value="Dr. Smith">Dr. Smith</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeBorrowModal()">Cancel</button>
                    <button type="submit" class="btn-primary" style="background: #dc2626; border-color: #dc2626;">Confirm Borrow</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT STATE & LOGIC -->
    <script>
        const defaultProducts = {
            gloves: {
                name: "Sterile Surgical Gloves (M)",
                balance: 120,
                unit: "pairs",
                badge: "green",
                standard: [
                    { date: "2026-07-10", bht: "BHT-10293", qty: 10, balance: 120, signature: "Sr. Dlamini", note: "Routine use" },
                    { date: "2026-07-09", bht: "BHT-10112", qty: 20, balance: 130, signature: "Dr. Nkosi", note: "OR setup" }
                ],
                emergency: [
                    { date: "2026-07-11", bht: "BHT-10992", qty: 5, balance: 115, signature: "Sr. Zulu", note: "Urgent trauma" }
                ],
                borrowed: []
            },
            scalpel: {
                name: "Disposable Scalpel Blades",
                balance: 45,
                unit: "pcs",
                badge: "orange",
                standard: [
                    { date: "2026-07-12", bht: "BHT-10245", qty: 5, balance: 45, signature: "Sr. Zulu", note: "Surgical kit" }
                ],
                emergency: [],
                borrowed: []
            },
            sutures: {
                name: "Absorbable Sutures 2-0",
                balance: 30,
                unit: "boxes",
                badge: "green",
                standard: [
                    { date: "2026-07-01", bht: "BHT-09221", qty: 2, balance: 30, signature: "Sr. Mokoena", note: "Routine closure" }
                ],
                emergency: [],
                borrowed: []
            }
        };

        let products = {};
        let currentProductKey = 'gloves';
        let activeTab = 'standard';

        function initApp() {
            const savedData = localStorage.getItem('ward_inventory_surgical_2');
            if (savedData) {
                products = JSON.parse(savedData);
                // Ensure all products have borrowed array
                Object.keys(products).forEach(key => {
                    if (!products[key].borrowed) {
                        products[key].borrowed = [];
                    }
                });
            } else {
                products = defaultProducts;
                localStorage.setItem('ward_inventory_surgical_2', JSON.stringify(products));
            }
            renderSidebar();
            selectProduct(currentProductKey);
        }

        function renderSidebar() {
            const listContainer = document.getElementById("medicineList");
            listContainer.innerHTML = "";

            Object.keys(products).forEach(key => {
                const product = products[key];
                const isActive = key === currentProductKey;

                const itemDiv = document.createElement("div");
                itemDiv.className = `medicine-item ${isActive ? 'active' : ''}`;
                itemDiv.id = `sidebar-item-${key}`;
                itemDiv.onclick = () => selectProduct(key);

                itemDiv.innerHTML = `
                    <span class="medicine-name">${product.name}</span>
                    <span class="badge ${product.badge}">${product.balance} ${product.unit}</span>
                `;
                listContainer.appendChild(itemDiv);
            });
        }

        function filterSidebar() {
            const searchVal = document.getElementById("sidebarSearch").value.toLowerCase();
            const items = document.querySelectorAll(".medicine-item");
            items.forEach(item => {
                const name = item.querySelector(".medicine-name").innerText.toLowerCase();
                if (name.includes(searchVal)) {
                    item.style.display = "flex";
                } else {
                    item.style.display = "none";
                }
            });
        }

        function selectProduct(key) {
            currentProductKey = key;
            const product = products[key];

            document.querySelectorAll(".medicine-item").forEach(item => item.classList.remove("active"));
            const activeItem = document.getElementById(`sidebar-item-${key}`);
            if (activeItem) activeItem.classList.add("active");

            document.getElementById("productTitle").innerText = product.name;
            document.getElementById("headerBalanceValue").innerText = `${product.balance} ${product.unit}`;

            updateTabLabels();
            renderTable();
        }

        function updateTabLabels() {
            const product = products[currentProductKey];
            const standardCount = product.standard.length;
            const borrowedCount = (product.borrowed || []).length;

            document.getElementById("tabAdmin").innerText = `Standard Requisitions (${standardCount})`;
            document.getElementById("tabBorrowed").innerText = `Borrowed Stock (${borrowedCount})`;
        }

        function switchTab(tab) {
            activeTab = tab;

            if (tab === 'standard') {
                document.getElementById("tabAdmin").classList.add("active");
                document.getElementById("tabBorrowed").classList.remove("active");
                document.getElementById("tableTitle").innerText = "Standard Requisitions Log";
                document.getElementById("addBtnLabel").innerText = "Add Record";
            } else if (tab === 'borrowed') {
                document.getElementById("tabAdmin").classList.remove("active");
                document.getElementById("tabBorrowed").classList.add("active");
                document.getElementById("tableTitle").innerText = "Borrowed Stock Log";
                document.getElementById("addBtnLabel").innerText = "Borrow Stock";
            }

            renderTable();
        }

        function formatDate(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length !== 3) return dateStr;
            const year = parts[0];
            const monthIdx = parseInt(parts[1], 10) - 1;
            const day = parseInt(parts[2], 10);
            
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return `${day < 10 ? '0' + day : day} ${months[monthIdx]} ${year}`;
        }

        function renderTable(searchQuery = '', dateQuery = '') {
            const product = products[currentProductKey];
            const tableHeader = document.getElementById("tableHeader");
            const tableBody = document.getElementById("tableBody");
            
            tableHeader.innerHTML = "";
            tableBody.innerHTML = "";

            const searchVal = searchQuery.toLowerCase();

            if (activeTab === 'borrowed') {
                tableHeader.innerHTML = `
                    <tr>
                        <th>Date</th>
                        <th>Name of First Aid Supplies</th>
                        <th>Balance After Borrowing</th>
                        <th>Borrowed Quantity</th>
                        <th>Staff Member (Borrower)</th>
                        <th>Signature/Confirmation</th>
                    </tr>
                `;
            } else {
                tableHeader.innerHTML = `
                    <tr>
                        <th>DATE</th>
                        <th>BHT</th>
                        <th>QUANTITY</th>
                        <th>BALANCE</th>
                        <th>SIGNATURE IN CHARGE</th>
                        <th>NOTE</th>
                    </tr>
                `;
            }

            let logs = [];
            if (activeTab === 'standard') {
                logs = product.standard;
            } else if (activeTab === 'borrowed') {
                logs = product.borrowed || [];
            }

            const filteredLogs = logs.filter(log => {
                let matchSearch = false;
                if (activeTab === 'borrowed') {
                    matchSearch = product.name.toLowerCase().includes(searchVal) ||
                                  (log.staff && log.staff.toLowerCase().includes(searchVal)) ||
                                  (log.signature && log.signature.toLowerCase().includes(searchVal));
                } else {
                    matchSearch = log.bht.toLowerCase().includes(searchVal) ||
                                  log.signature.toLowerCase().includes(searchVal) ||
                                  log.note.toLowerCase().includes(searchVal);
                }
                const matchDate = !dateQuery || log.date === dateQuery;
                return matchSearch && matchDate;
            });

            if (filteredLogs.length === 0) {
                const colSpan = 6;
                tableBody.innerHTML = `<tr><td colspan="${colSpan}" class="empty-state">No records found matching filters.</td></tr>`;
            } else {
                filteredLogs.forEach(log => {
                    if (activeTab === 'borrowed') {
                        tableBody.innerHTML += `
                            <tr>
                                <td>${formatDate(log.date)}</td>
                                <td><strong>${product.name}</strong></td>
                                <td><span class="balance-val">${log.balance} ${product.unit}</span></td>
                                <td><span style="color: #ef4444; font-weight: 600;">-${log.qty} ${product.unit}</span></td>
                                <td>${log.staff}</td>
                                <td class="signature-text">${log.signature}</td>
                            </tr>
                        `;
                    } else {
                        tableBody.innerHTML += `
                            <tr>
                                <td>${formatDate(log.date)}</td>
                                <td><strong>${log.bht}</strong></td>
                                <td>${log.qty} ${product.unit}</td>
                                <td><span class="balance-val">${log.balance} ${product.unit}</span></td>
                                <td class="signature-text">${log.signature}</td>
                                <td>${log.note}</td>
                            </tr>
                        `;
                    }
                });
            }
        }

        function filterLogs() {
            const searchVal = document.getElementById("logSearch").value;
            const dateVal = document.getElementById("logDateFilter").value;
            renderTable(searchVal, dateVal);
        }

        function clearFilters() {
            document.getElementById("logSearch").value = "";
            document.getElementById("logDateFilter").value = "";
            renderTable();
        }

        function openModal() {
            const product = products[currentProductKey];
            
            if (activeTab === 'borrowed') {
                const modal = document.getElementById("borrowRecordModal");
                const borrowQtyUnitLabel = document.getElementById("borrowQtyUnitLabel");
                
                // Reset Form
                document.getElementById("borrowForm").reset();
                
                // Set default date to today
                const today = new Date().toISOString().split('T')[0];
                document.getElementById("borrowDate").value = today;
                
                borrowQtyUnitLabel.innerText = product.unit;
                modal.classList.add("active");
                return;
            }

            const modal = document.getElementById("addRecordModal");
            const modalTitle = document.getElementById("modalTitle");
            const qtyUnitLabel = document.getElementById("qtyUnitLabel");

            document.getElementById("recordForm").reset();
            
            const today = new Date().toISOString().split('T')[0];
            document.getElementById("recordDate").value = today;

            qtyUnitLabel.innerText = product.unit;
            modalTitle.innerText = "Add Record";

            modal.classList.add("active");
        }

        function closeModal() {
            document.getElementById("addRecordModal").classList.remove("active");
        }

        function closeBorrowModal() {
            document.getElementById("borrowRecordModal").classList.remove("active");
        }

        function saveRecord(event) {
            event.preventDefault();

            const product = products[currentProductKey];
            const dateVal = document.getElementById("recordDate").value;
            const bhtVal = document.getElementById("recordBht").value;
            const qtyVal = parseInt(document.getElementById("recordQty").value, 10);
            const sigVal = document.getElementById("recordSig").value;
            const noteVal = document.getElementById("recordNote").value;

            if (isNaN(qtyVal) || qtyVal <= 0) {
                alert("Please enter a valid quantity.");
                return;
            }

            if (qtyVal > product.balance) {
                alert(`Insufficient stock! Available stock is only ${product.balance} ${product.unit}.`);
                return;
            }

            // Deduct from balance since BHT represents usage/issues
            product.balance -= qtyVal;

            const newLog = {
                date: dateVal,
                bht: bhtVal,
                qty: qtyVal,
                balance: product.balance,
                signature: sigVal,
                note: noteVal
            };

            // Prepend standard log
            product.standard.unshift(newLog);

            localStorage.setItem('ward_inventory_surgical_2', JSON.stringify(products));

            renderSidebar();
            selectProduct(currentProductKey);
            closeModal();
        }

        // Save a borrow record
        function saveBorrowRecord(event) {
            event.preventDefault();

            const product = products[currentProductKey];
            const dateVal = document.getElementById("borrowDate").value;
            const qtyVal = parseInt(document.getElementById("borrowQty").value, 10);
            const staffVal = document.getElementById("borrowStaff").value;
            const sigVal = document.getElementById("borrowSig").value;

            if (isNaN(qtyVal) || qtyVal <= 0) {
                alert("Please enter a valid quantity.");
                return;
            }

            if (qtyVal > product.balance) {
                alert(`Insufficient stock! Available stock is only ${product.balance} ${product.unit}.`);
                return;
            }

            // Deduct stock balance
            product.balance -= qtyVal;

            // Construct new borrow log record
            const newLog = {
                date: dateVal,
                qty: qtyVal,
                balance: product.balance,
                staff: staffVal,
                signature: sigVal
            };

            // Prepend log to borrowed list
            if (!product.borrowed) {
                product.borrowed = [];
            }
            product.borrowed.unshift(newLog);

            // Save updated products list to localStorage
            localStorage.setItem('ward_inventory_surgical_2', JSON.stringify(products));

            // Refresh UI
            renderSidebar();
            selectProduct(currentProductKey);
            closeBorrowModal();
        }

        document.getElementById("addRecordModal").addEventListener("click", function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        document.getElementById("borrowRecordModal").addEventListener("click", function(e) {
            if (e.target === this) {
                closeBorrowModal();
            }
        });

        window.onload = initApp;
    </script>
</body>
</html>