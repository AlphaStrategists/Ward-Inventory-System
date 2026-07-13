<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ward Inventory System</title>

    <style>
        .ward-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #1e3a8a;
            padding: 30px 40px;           /* wider = more inner spacing */
            font-family: 'Inter', 'Segoe UI', sans-serif;
            margin: 20px 24px 0 24px;     /* space around the bar (top/sides) */
            border-radius: 14px;          /* rounded corners */
        }

        .ward-header__left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .ward-header__icon {
            width: 40px;
            height: 40px;
            background-color: #ef4444;
            border-radius: 10px;
            color: #fff;
            font-size: 22px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ward-header__title {
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }

        .ward-header__subtitle {
            color: #cbd5e1;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin: 2px 0 0 0;
        }

        .ward-header__right {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .ward-header__date {
            color: #e2e8f0;
            font-size: 14px;
        }

        .ward-header__badge {
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 999px;
        }

        

        /* ===== NEW: page background + spacing ===== */
        body {
            margin: 0;
            background-color: #eef1f7;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        .page-content {
            padding: 24px 32px;
        }

        /* ===== NEW: Low Stock stat card ===== */
        .stat-card {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            background-color: #fff;
            border-left: 4px solid #d97706; /* amber accent */
            border-radius: 8px;
            padding: 16px 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .stat-card__icon {
            width: 40px;
            height: 40px;
            background-color: #fef3c7; /* light amber */
            color: #d97706;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .stat-card__value {
            font-size: 26px;
            font-weight: 700;
            color: #d97706;
            line-height: 1;
        }

        .stat-card__label {
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        /* ===== NEW: search bar + Add Item toolbar ===== */
        .toolbar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 20px;
        }

        .search-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
        }

        .search-input:focus {
            border-color: #1e3a8a;
        }

        .btn-add-item {
            display: flex;
            align-items: center;
            gap: 6px;
            background-color: #ef4444;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            border: none;
            border-radius: 8px;
            padding: 12px 18px;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-add-item:hover {
            background-color: #dc2626;
        }

        /* ===== NEW: table ===== */
        .table-wrapper {
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        thead {
            background-color: #1e3a8a;
        }

        thead th {
            color: #fff;
            text-align: left;
            padding: 14px 20px;
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
        }

        thead th .sort-icon {
            font-size: 11px;
            margin-left: 4px;
            opacity: 0.7;
        }

        tbody td {
            padding: 14px 20px;
            border-bottom: 1px solid #f0f1f5;
            color: #374151;
        }

        tbody tr:hover {
            background-color: #f9fafb;
        }

        .col-actions {
            display: flex;
            gap: 8px;
        }

        .btn-edit,
        .btn-delete {
            border: none;
            border-radius: 6px;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-edit {
            background-color: #1e3a8a;
            color: #fff;
        }

        .btn-edit:hover {
            background-color: #1e40af;
        }

        .btn-delete {
            background-color: #fee2e2;
            color: #ef4444;
        }

        .btn-delete:hover {
            background-color: #fecaca;
        }
        /* ===== NEW: Add Item modal ===== */
        .modal-overlay {
            display: none; /* hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background-color: #fff;
            border-radius: 10px;
            width: 400px;
            padding: 24px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .modal-box h2 {
            margin: 0 0 20px 0;
            font-size: 18px;
            color: #1e3a8a;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #1e3a8a;
        }

        .form-group input[readonly] {
            background-color: #f3f4f6;
            color: #6b7280;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-cancel {
            background-color: #f3f4f6;
            color: #374151;
            border: none;
            border-radius: 6px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-submit {
            background-color: #1e3a8a;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-submit:hover {
            background-color: #1e40af;
        }
    </style>
</head>
<body>

    <header class="ward-header">
        <div class="ward-header__left">
            <div class="ward-header__icon">+</div>
            <div class="ward-header__titles">
                <h1 class="ward-header__title">Ward Inventory System</h1>
                <p class="ward-header__subtitle">GENERAL INVENTORY — MEDICAL WARD B</p>
            </div>
        </div>

        <div class="ward-header__right">
            <span class="ward-header__date">{{ now()->format('D d M Y') }}</span>
            <span class="ward-header__badge">Admin</span>
        </div>
    </header>
    <div class="ward-header__accent-line"></div>

    <div class="page-content">

        <!-- Low Stock stat card -->
        <div class="stat-card">
            <div class="stat-card__icon">⚠</div>
            <div>
                <div class="stat-card__value">4</div>
                <div class="stat-card__label">LOW STOCK ITEMS</div>
            </div>
        </div>

        <!-- Search bar + Add Item button -->
        <div class="toolbar">
            <input
                type="text"
                class="search-input"
                placeholder="Search by item or code..."
            >

            <button type="button" class="btn-add-item">
                + Add Item
            </button>
        </div>
        <!-- Inventory table -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ITEM <span class="sort-icon">⇅</span></th>
                        <th>DATE <span class="sort-icon">⇅</span></th>
                        <th>CODE <span class="sort-icon">⇅</span></th>
                        <th>RECEIVED <span class="sort-icon">⇅</span></th>
                        <th>ISSUED <span class="sort-icon">⇅</span></th>
                        <th>BALANCE <span class="sort-icon">⇅</span></th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
<tbody>
                    
    <tr>
        <td>01</td>
        <td>Paracetamol 500mg</td>
        <td>2026-07-01</td>
        <td>MED-001</td>
        <td>500</td>
        <td>120</td>
        <td>380</td>
        <td>
            <div class="col-actions">
                <button type="button" class="btn-edit">Edit</button>
                <button type="button" class="btn-delete">Del</button>
            </div>
        </td>
    </tr>
</tbody>
                
            </table>
        </div>

    </div>

    <!-- Inventory table -->
    <div class="table-wrapper">
        ... (unchanged)
    </div>

</div>
<!-- Add Item modal -->
    <div class="modal-overlay" id="addItemModal">
        <div class="modal-box">
            <h2>Add New Item</h2>

            <form id="addItemForm">
                <div class="form-group">
                    <label for="itemName">Item</label>
                    <input type="text" id="itemName" name="item" required>
                </div>

                <div class="form-group">
                    <label for="itemDate">Date</label>
                    <input type="date" id="itemDate" name="date" readonly>
                </div>

                <div class="form-group">
                    <label for="itemCode">Code</label>
                    <input type="text" id="itemCode" name="code" required>
                </div>

                <div class="form-group">
                    <label for="itemAmount">Amount</label>
                    <input type="number" id="itemAmount" name="amount" required>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelAddItem">Cancel</button>
                    <button type="submit" class="btn-submit">Add</button>
                </div>
            </form>
        </div>
    </div>

<script>
    // Grab all edit buttons and attach a click handler to each
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');
            const itemCode = row.children[3].textContent; // CODE column
            alert('Edit clicked for: ' + itemCode);

            // Later, replace the alert above with something like:
            // window.location.href = '/inventory/edit/' + itemCode;
        });
    });

    // Grab all delete buttons and attach a click handler to each
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');
            const itemName = row.children[1].textContent; // ITEM column

            const confirmed = confirm('Delete "' + itemName + '"?');
            if (confirmed) {
                row.remove(); // just removes it visually for now
                // Later: send a real delete request to the server instead
            }
        });
    });
    // Open modal when "Add Item" button is clicked
    document.querySelector('.btn-add-item').addEventListener('click', function () {
        document.getElementById('addItemModal').classList.add('active');

        // Auto-fill today's date
        const today = new Date().toISOString().split('T')[0]; // format: YYYY-MM-DD
        document.getElementById('itemDate').value = today;
    });

    // Close modal on Cancel
    document.getElementById('cancelAddItem').addEventListener('click', function () {
        document.getElementById('addItemModal').classList.remove('active');
    });

    // Handle form submit (placeholder for now)
    document.getElementById('addItemForm').addEventListener('submit', function (e) {
        e.preventDefault(); // stop page reload

        const item = document.getElementById('itemName').value;
        const date = document.getElementById('itemDate').value;
        const code = document.getElementById('itemCode').value;
        const amount = document.getElementById('itemAmount').value;

        console.log({ item, date, code, amount });
        alert('Item added (not yet saved to database): ' + item);

        // Later: send this data to Laravel via fetch() or a real form POST
        document.getElementById('addItemModal').classList.remove('active');
        this.reset();
    });
</script>

</body>
</html>

