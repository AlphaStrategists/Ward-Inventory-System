<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ward Inventory</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f3f5f9;
        }

        .page {
            display: flex;
            min-height: 100vh;
        }

        .page__content {
            flex: 1;
            padding: 30px 40px;
        }

        .sidebar {
            background: #ffffff;
            width: 280px;
            min-height: 100vh;
            border-right: 1px solid #e8ebf0;
            padding: 20px 0;
            flex-shrink: 0;
        }

        .sidebar__header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 20px 16px 20px;
            border-bottom: 1px solid #e8ebf0;
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            background: #2563eb;
            color: #ffffff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .sidebar__title {
            color: #1e2a5e;
            font-size: 15px;
            font-weight: 700;
            margin: 0;
        }

        .sidebar__subtitle {
            color: #9aa3b5;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin: 2px 0 0 0;
        }

        .sidebar__list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .sidebar__empty {
            padding: 20px;
            font-size: 13px;
            color: #9aa3b5;
        }

        .sidebar__item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 14px 20px;
            cursor: pointer;
            border-left: 3px solid transparent;
            text-decoration: none;
        }

        .sidebar__item:hover {
            background: #f7f9fc;
        }

        .sidebar__item--active {
            background: #eef2ff;
            border-left: 3px solid #2b4de8;
        }

        .sidebar__item--active .sidebar__name {
            color: #2b4de8;
            font-weight: 600;
        }

        .sidebar__name {
            color: #33394a;
            font-size: 14px;
            line-height: 1.3;
        }

        .badge {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            white-space: nowrap;
            color: #ffffff;
            flex-shrink: 0;
        }

        .badge--green  { background: #16a34a; }
        .badge--orange { background: #f97316; }
        .badge--yellow { background: #eab308; }
        .badge--red    { background: #ef4444; }

        .stock-header {
            background: linear-gradient(135deg, #2b4de8, #1e3bcf);
            border-radius: 16px;
            padding: 32px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .stock-header__title {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 6px 0;
        }

        .stock-header__subtitle {
            color: rgba(255, 255, 255, 0.75);
            font-size: 14px;
            margin: 0;
        }

        .stock-header__badge {
            background: #ffffff;
            border-radius: 14px;
            padding: 14px 28px;
            text-align: center;
            min-width: 120px;
        }

        .stock-header__count {
            display: block;
            color: #1e2a5e;
            font-size: 28px;
            font-weight: 800;
            line-height: 1;
        }

        .stock-header__label {
            display: block;
            color: #8a93a8;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        .tabs-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 14px 16px;
            margin-top: 24px;
            display: flex;
            gap: 10px;
        }

        .tab-btn {
            border: none;
            border-radius: 10px;
            padding: 12px 22px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            background: transparent;
            color: #5a6478;
        }

        .tab-btn:hover {
            background: #f2f4f9;
        }

        .tab-btn--active {
            background: #1e2a5e;
            color: #ffffff;
        }

        .tab-btn--active:hover {
            background: #1e2a5e;
        }

        .requisitions {
            background: #ffffff;
            border-radius: 16px;
            padding: 28px 32px;
            margin-top: 24px;
            display: none; 
        }

        .requisitions--visible {
            display: block;
        }

        .requisitions__header {
            margin-bottom: 20px;
        }

        .requisitions__title {
            font-size: 18px;
            font-weight: 700;
            color: #1e2a5e;
            margin: 0;
        }

        .requisitions__table-wrap {
            overflow-x: auto;
        }

        .requisitions__empty {
            padding: 20px 12px;
            font-size: 14px;
            color: #9aa3b5;
        }

        .requisitions-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        .requisitions-table th {
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.4px;
            color: #9aa3b5;
            text-transform: uppercase;
            padding: 0 12px 12px 12px;
            border-bottom: 1px solid #e8ebf0;
            white-space: nowrap;
        }

        .requisitions-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #f0f2f7;
            font-size: 14px;
            color: #33394a;
            white-space: nowrap;
        }

        .requisitions-table tr:last-child td {
            border-bottom: none;
        }

        .req-qty {
            font-weight: 700;
        }

        .req-balance {
            font-weight: 700;
            color: #1e2a5e;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(30, 42, 94, 0.35);
            align-items: center;
            justify-content: center;
            z-index: 100;
            padding: 20px;
        }

        .modal-overlay--visible {
            display: flex;
        }

        .modal {
            background: #ffffff;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            padding: 28px 32px 24px 32px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .modal__title {
            font-size: 20px;
            font-weight: 700;
            color: #1e2a5e;
            margin: 0;
        }

        .modal__close {
            background: none;
            border: none;
            font-size: 20px;
            color: #9aa3b5;
            cursor: pointer;
            line-height: 1;
            padding: 4px;
        }

        .modal__close:hover {
            color: #5a6478;
        }

        .modal__field {
            margin-bottom: 18px;
        }

        .modal__label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #33394a;
            margin-bottom: 8px;
        }

        .modal__input-wrap {
            position: relative;
        }

        .modal__field input,
        .modal__field select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d8dde6;
            border-radius: 8px;
            font-size: 14px;
            color: #33394a;
            font-family: inherit;
            background: #ffffff;
        }

        .modal__field input:focus,
        .modal__field select:focus {
            outline: none;
            border-color: #2b4de8;
        }

        .modal__unit {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 13px;
            color: #9aa3b5;
            pointer-events: none;
        }

        .modal__preview {
            background: #f7f9fc;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 13px;
            color: #5a6478;
            margin-bottom: 18px;
        }

        .modal__preview strong {
            color: #1e2a5e;
        }

        .modal__actions {
            display: flex;
            justify-content: flex-end;
            gap: 14px;
            margin-top: 8px;
        }

        .modal__cancel {
            background: none;
            border: none;
            font-size: 14px;
            font-weight: 600;
            color: #1e2a5e;
            cursor: pointer;
            padding: 12px 8px;
        }

        .modal__save {
            background: #2b4de8;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 12px 22px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .modal__save:hover {
            background: #1e3bcf;
        }

        .modal__save:disabled {
            background: #aab4d4;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-header-box">
            <div class="logo-container">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                    </svg>
                </div>
                <div class="logo-text">
                    <span class="logo-title">Ward Inventory</span>
                    <span class="logo-subtitle">SURGICAL 2</span>
                </div>
            </div>

            <ul class="sidebar__list" id="sidebarList">
            </ul>
        </div>

        <main class="page__content">

            <div class="stock-header">
                <div class="stock-header__info">
                    <h1 class="stock-header__title" id="headerTitle">No item selected</h1>
                    <p class="stock-header__subtitle">Current Ward Stock Balance</p>
                </div>

                <div class="stock-header__badge">
                    <span class="stock-header__count" id="headerCount">0</span>
                    <span class="stock-header__label">AVAILABLE</span>
                </div>
            </div>

            <div class="tabs-card" id="tabsWrap">
                <button class="tab-btn tab-btn--active" data-tab="add">Add Requisition</button>
                <button class="tab-btn" data-tab="provided">Provided Requisition</button>
            </div>

            <div class="requisitions requisitions--visible" id="addRequisitionsCard">
                <div class="requisitions__header">
                    <h2 class="requisitions__title">Add Requisitions Log</h2>
                </div>

                <div class="requisitions__table-wrap">
                    <table class="requisitions-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>BHT</th>
                                <th>Quantity</th>
                                <th>Balance</th>
                                <th>Signature in Charge</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody id="addRequisitionsBody">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="requisitions" id="providedRequisitionsCard">
                <div class="requisitions__header">
                    <h2 class="requisitions__title">Provided Requisitions Log</h2>
                </div>

                <div class="requisitions__table-wrap">
                    <table class="requisitions-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Name of First Aid Supplies</th>
                                <th>Balance</th>
                                <th>Requested Quantity</th>
                                <th>Confirmation Signature</th>
                            </tr>
                        </thead>
                        <tbody id="providedRequisitionsBody">
                        </tbody>
                    </table>
                </div>
            </div>

        </main>

    </div>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <div class="modal__header">
                <h2 class="modal__title" id="modalTitle">Add Requisition</h2>
                <button class="modal__close" id="modalClose">&times;</button>
            </div>

            <div class="modal__preview" id="modalPreview">
                Current balance: <strong id="modalCurrentBalance">0</strong>
            </div>

            <div class="modal__field">
                <label class="modal__label">Date</label>
                <input type="date" id="formDate">
            </div>

            <div class="modal__field" id="fieldRequestedQty">
                <label class="modal__label" id="labelRequestedQty">Requested Quantity</label>
                <div class="modal__input-wrap">
                    <input type="number" id="formRequestedQty" placeholder="e.g. 200" min="0">
                    <span class="modal__unit" id="formRequestedUnit"></span>
                </div>
            </div>

            <div class="modal__field" id="fieldRequestSignature">
                <label class="modal__label" id="labelRequestSignature">Confirmation Signature (Requested)</label>
                <select id="formRequestSignature">
                    <option value="">Select name</option>
                </select>
            </div>

            <div class="modal__field" id="fieldReceivedQty">
                <label class="modal__label">Received Quantity</label>
                <div class="modal__input-wrap">
                    <input type="number" id="formReceivedQty" placeholder="e.g. 200" min="0">
                    <span class="modal__unit" id="formReceivedUnit"></span>
                </div>
            </div>

            <div class="modal__field" id="fieldReceivedSignature">
                <label class="modal__label">Confirmation Signature (Received)</label>
                <select id="formReceivedSignature">
                    <option value="">Select name</option>
                </select>
            </div>

            <div class="modal__actions">
                <button class="modal__cancel" id="modalCancel">Cancel</button>
                <button class="modal__save" id="modalSave">Save Requisition</button>
            </div>
        </div>
    </div>

    <script>
        const API_BASE = '/api';

        let stockItems = [];
        let staffNames = [];
        let addRequisitions = [];
        let providedRequisitions = [];

        function colorForQuantity(qty) {
            if (qty <= 10) return 'red';
            if (qty <= 30) return 'yellow';
            return 'green';
        }

        let activeSlug = null;

        const sidebarList = document.getElementById('sidebarList');
        const headerTitle = document.getElementById('headerTitle');
        const headerCount = document.getElementById('headerCount');

        function renderSidebar() {
            sidebarList.innerHTML = '';

            if (stockItems.length === 0) {
                sidebarList.innerHTML = '<li class="sidebar__empty">No stock items yet</li>';
                return;
            }

            stockItems.forEach((item) => {
                const li = document.createElement('li');
                const isActive = item.slug === activeSlug;

                li.innerHTML = `
                    <a href="#" class="sidebar__item ${isActive ? 'sidebar__item--active' : ''}" data-slug="${item.slug}">
                        <span class="sidebar__name">${item.name}</span>
                        <span class="badge badge--${item.color}">${item.quantity} ${item.unit}</span>
                    </a>
                `;

                sidebarList.appendChild(li);
            });
        }

        function renderHeader() {
            const currentItem = stockItems.find((item) => item.slug === activeSlug);

            if (!currentItem) {
                headerTitle.textContent = 'No item selected';
                headerCount.textContent = '0';
                return;
            }

            headerTitle.textContent = currentItem.name;
            headerCount.textContent = currentItem.quantity;
        }

        sidebarList.addEventListener('click', async (event) => {
            event.preventDefault();
            const link = event.target.closest('.sidebar__item');
            if (!link) return;

            activeSlug = link.dataset.slug;
            renderSidebar();
            renderHeader();

            try {
                const reqs = await fetchRequisitions(activeSlug);
                addRequisitions = reqs.add || [];
                providedRequisitions = reqs.provided || [];
                renderAddRequisitions();
                renderProvidedRequisitions();
            } catch (err) {
                console.error('Failed to load requisitions for this item:', err);
            }
        });

        let activeTab = 'add';
        const tabsWrap = document.getElementById('tabsWrap');

        const addRequisitionsCard = document.getElementById('addRequisitionsCard');
        const providedRequisitionsCard = document.getElementById('providedRequisitionsCard');

        function renderTabs() {
            document.querySelectorAll('.tab-btn').forEach((btn) => {
                btn.classList.toggle('tab-btn--active', btn.dataset.tab === activeTab);
            });

            addRequisitionsCard.classList.toggle('requisitions--visible', activeTab === 'add');
            providedRequisitionsCard.classList.toggle('requisitions--visible', activeTab === 'provided');
        }

        const modalOverlay = document.getElementById('modalOverlay');
        const modalTitle = document.getElementById('modalTitle');
        const modalCurrentBalance = document.getElementById('modalCurrentBalance');
        const modalClose = document.getElementById('modalClose');
        const modalCancel = document.getElementById('modalCancel');
        const modalSave = document.getElementById('modalSave');

        const formDate = document.getElementById('formDate');
        const formRequestedQty = document.getElementById('formRequestedQty');
        const formRequestedUnit = document.getElementById('formRequestedUnit');
        const formRequestSignature = document.getElementById('formRequestSignature');
        const formReceivedQty = document.getElementById('formReceivedQty');
        const formReceivedUnit = document.getElementById('formReceivedUnit');
        const formReceivedSignature = document.getElementById('formReceivedSignature');

        const labelRequestedQty = document.getElementById('labelRequestedQty');
        const labelRequestSignature = document.getElementById('labelRequestSignature');
        const fieldReceivedQty = document.getElementById('fieldReceivedQty');
        const fieldReceivedSignature = document.getElementById('fieldReceivedSignature');

        function populateSignatureDropdowns() {
            [formRequestSignature, formReceivedSignature].forEach((select) => {
                select.innerHTML = '<option value="">Select name</option>';

                staffNames.forEach((name) => {
                    const option = document.createElement('option');
                    option.value = name;
                    option.textContent = name;
                    select.appendChild(option);
                });
            });
        }

        function clearModalForm() {
            formDate.value = '';
            formRequestedQty.value = '';
            formRequestSignature.value = '';
            formReceivedQty.value = '';
            formReceivedSignature.value = '';
        }

        function openModal() {
            if (!activeSlug) {
                alert('Select a stock item from the sidebar first.');
                return;
            }

            const currentItem = stockItems.find((item) => item.slug === activeSlug);

            modalCurrentBalance.textContent = `${currentItem.quantity} ${currentItem.unit}`;
            formRequestedUnit.textContent = currentItem.unit;
            formReceivedUnit.textContent = currentItem.unit;

            if (activeTab === 'add') {
                modalTitle.textContent = 'Add Requisition';
                labelRequestedQty.textContent = 'Requested Quantity';
                labelRequestSignature.textContent = 'Confirmation Signature (Requested)';
                fieldReceivedQty.style.display = 'block';
                fieldReceivedSignature.style.display = 'block';
            } else {
                modalTitle.textContent = 'Provided Requisition';
                labelRequestedQty.textContent = 'Requested Quantity';
                labelRequestSignature.textContent = 'Confirmation Signature';
                fieldReceivedQty.style.display = 'none';
                fieldReceivedSignature.style.display = 'none';
            }

            modalOverlay.classList.add('modal-overlay--visible');
        }

        function closeModal() {
            modalOverlay.classList.remove('modal-overlay--visible');
        }

        tabsWrap.addEventListener('click', (event) => {
            const btn = event.target.closest('.tab-btn');
            if (!btn) return;

            activeTab = btn.dataset.tab;
            renderTabs();
            openModal();
        });

        modalClose.addEventListener('click', closeModal);
        modalCancel.addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', (event) => {
            if (event.target === modalOverlay) closeModal();
        });

        const addRequisitionsBody = document.getElementById('addRequisitionsBody');
        const providedRequisitionsBody = document.getElementById('providedRequisitionsBody');

        function renderAddRequisitions() {
            if (addRequisitions.length === 0) {
                addRequisitionsBody.innerHTML = '<tr><td class="requisitions__empty" colspan="7">No requisitions logged yet</td></tr>';
                return;
            }

            addRequisitionsBody.innerHTML = '';

            addRequisitions.forEach((req) => {
                const tr = document.createElement('tr');

                tr.innerHTML = `
                    <td>${req.date}</td>
                    <td>${req.itemName}</td>
                    <td class="req-balance">${req.balanceAfter} ${req.unit}</td>
                    <td class="req-qty">${req.requestedQty} ${req.unit}</td>
                    <td>${req.requestSignature}</td>
                    <td class="req-qty">${req.receivedQty} ${req.unit}</td>
                    <td>${req.receivedSignature}</td>
                `;

                addRequisitionsBody.appendChild(tr);
            });
        }

        function renderProvidedRequisitions() {
            if (providedRequisitions.length === 0) {
                providedRequisitionsBody.innerHTML = '<tr><td class="requisitions__empty" colspan="5">No requisitions logged yet</td></tr>';
                return;
            }

            providedRequisitionsBody.innerHTML = '';

            providedRequisitions.forEach((req) => {
                const tr = document.createElement('tr');

                tr.innerHTML = `
                    <td>${req.date}</td>
                    <td>${req.itemName}</td>
                    <td class="req-balance">${req.balanceAfter} ${req.unit}</td>
                    <td class="req-qty">${req.requestedQty} ${req.unit}</td>
                    <td>${req.signature}</td>
                `;

                providedRequisitionsBody.appendChild(tr);
            });
        }

        function formatDate(rawDate) {
            if (!rawDate) return '';
            const parsed = new Date(rawDate + 'T00:00:00');
            return parsed.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        async function fetchStockItems() {
            const res = await fetch(`${API_BASE}/stock-items`);
            if (!res.ok) throw new Error('Failed to load stock items');
            return res.json();
        }

        async function fetchStaffNames() {
            const res = await fetch(`${API_BASE}/staff`);
            if (!res.ok) throw new Error('Failed to load staff');
            return res.json();
        }

        async function fetchRequisitions(slug) {
            const res = await fetch(`${API_BASE}/stock-items/${slug}/requisitions`);
            if (!res.ok) throw new Error('Failed to load requisitions');
            return res.json(); 
        }

        async function saveRequisitionToDb(slug, tab, payload) {
            const res = await fetch(`${API_BASE}/stock-items/${slug}/requisitions/${tab}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            if (!res.ok) throw new Error('Failed to save requisition');
            return res.json(); 
        }

        modalSave.addEventListener('click', async () => {
            const currentItem = stockItems.find((item) => item.slug === activeSlug);
            if (!currentItem) return;

            modalSave.disabled = true;

            try {
                if (activeTab === 'add') {
                    const payload = {
                        date: formDate.value,
                        requestedQty: parseInt(formRequestedQty.value, 10) || 0,
                        requestSignature: formRequestSignature.value || null,
                        receivedQty: parseInt(formReceivedQty.value, 10) || 0,
                        receivedSignature: formReceivedSignature.value || null,
                    };

                    const result = await saveRequisitionToDb(activeSlug, 'add', payload);

                    currentItem.quantity = result.item.quantity;
                    currentItem.color = colorForQuantity(currentItem.quantity);

                    addRequisitions.unshift({
                        date: formatDate(payload.date) || '—',
                        itemName: currentItem.name,
                        unit: currentItem.unit,
                        requestedQty: payload.requestedQty,
                        requestSignature: payload.requestSignature || '—',
                        receivedQty: payload.receivedQty,
                        receivedSignature: payload.receivedSignature || '—',
                        balanceAfter: currentItem.quantity,
                    });

                    renderAddRequisitions();
                } else {
                    const payload = {
                        date: formDate.value,
                        providedQty: parseInt(formRequestedQty.value, 10) || 0,
                        signature: formRequestSignature.value || null,
                    };

                    const result = await saveRequisitionToDb(activeSlug, 'provided', payload);

                    currentItem.quantity = result.item.quantity;
                    currentItem.color = colorForQuantity(currentItem.quantity);

                    providedRequisitions.unshift({
                        date: formatDate(payload.date) || '—',
                        itemName: currentItem.name,
                        unit: currentItem.unit,
                        requestedQty: payload.providedQty,
                        signature: payload.signature || '—',
                        balanceAfter: currentItem.quantity,
                    });

                    renderProvidedRequisitions();
                }

                renderSidebar();
                renderHeader();
                clearModalForm();
                closeModal();
            } catch (err) {
                console.error(err);
                alert('Could not save this requisition. Please try again.');
            } finally {
                modalSave.disabled = false;
            }
        });

        async function initFromDatabase() {
            try {
                const [items, staff] = await Promise.all([
                    fetchStockItems(),
                    fetchStaffNames(),
                ]);

                stockItems = items;
                staffNames = staff;

                populateSignatureDropdowns();
                renderSidebar();

                if (stockItems.length > 0) {
                    activeSlug = stockItems[0].slug;
                    const reqs = await fetchRequisitions(activeSlug);
                    addRequisitions = reqs.add || [];
                    providedRequisitions = reqs.provided || [];
                }

                renderSidebar();
                renderHeader();
                renderAddRequisitions();
                renderProvidedRequisitions();
                renderTabs();
            } catch (err) {
                console.error('Failed to load data from the database:', err);
                renderSidebar();
                renderHeader();
                renderAddRequisitions();
                renderProvidedRequisitions();
                renderTabs();
            }
        }

        initFromDatabase();
    </script>

</body>
</html>