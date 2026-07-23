<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient List</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .ward-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #1e3a8a;
            padding: 30px 40px;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            margin: 20px 24px 0 24px;
            border-radius: 14px;
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


        body {
            margin: 0;
            background-color: #eef1f7;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        .page-content {
            padding: 24px 32px;
        }

        /* Toolbar (search + add button) — same structure as inventory page */
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

        .btn-add-patient {
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

        .btn-add-patient:hover {
            background-color: #dc2626;
        }

        /* Table — same structure as inventory page */
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
        /* ===== NEW: Add Patient modal ===== */
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
                <h1 class="ward-header__title">Patient Records</h1>
                <p class="ward-header__subtitle">MEDICAL WARDS 47 & 48</p>
            </div>
        </div>

        <div class="ward-header__right">
            <span class="ward-header__date">{{ now()->format('D d M Y') }}</span>
            <span class="ward-header__badge">Admin</span>
        </div>
    </header>
    <div class="ward-header__accent-line"></div>

    <div class="page-content">

        <!-- Search bar + Add Patient button -->
        <div class="toolbar">
            <input
                type="text"
                class="search-input"
                placeholder="Search by name or NIC..."
            >

            <button type="button" class="btn-add-patient">
                + Add Patient
            </button>
        </div>

        <!-- Patient table -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>PATIENT NAME <span class="sort-icon">⇅</span></th>
                        <th>NIC <span class="sort-icon">⇅</span></th>
                        <th>BEDHEAD NUMBER <span class="sort-icon">⇅</span></th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="patientTableBody">
    @foreach ($patients as $patient)
        <tr data-id="{{ $patient->id }}">
            <td>{{ $loop->iteration }}</td>
            <td>{{ $patient->name }}</td>
            <td>{{ $patient->nic }}</td>
            <td>{{ $patient->bedhead_number }}</td>
            <td>
                <div class="col-actions">
                    <button type="button" class="btn-edit">Edit</button>
                    <button type="button" class="btn-delete">Del</button>
                </div>
            </td>
        </tr>
    @endforeach
</tbody>
            </table>
        </div>

    </div>
    <!-- Add Item modal -->
    <div class="modal-overlay" id="addPatientModal">
        <div class="modal-box">
            <h2>Add New Patient</h2>

            <form id="addPatientForm">
                <div class="form-group">
                    <label for="patientName">Patient Name</label>
                    <input type="text" id="patientName" name="patient" required>
                </div>

                <div class="form-group">
                    <label for="admitDate">Admit Date</label>
                    <input type="date" id="admitDate" name="date" readonly>
                </div>

                <div class="form-group">
                    <label for="nic">NIC Number</label>
                    <input type="text" id="nic" name="nic" required>
                </div>

                <div class="form-group">
                    <label for="bedheadNum">Bed Head Number</label>
                    <input type="number" id="bedheadNum" name="bedheadNum" required>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelAddPatient">Cancel</button>
                    <button type="submit" class="btn-submit">Add</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Open modal
    document.querySelector('.btn-add-patient').addEventListener('click', function () {
        document.getElementById('addPatientModal').classList.add('active');
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('admitDate').value = today;
    });

    // Close modal
    document.getElementById('cancelAddPatient').addEventListener('click', function () {
        document.getElementById('addPatientModal').classList.remove('active');
    });

    // Add patient — real POST request
    document.getElementById('addPatientForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const name = document.getElementById('patientName').value;
        const admit_date = document.getElementById('admitDate').value;
        const nic = document.getElementById('nic').value;
        const bedhead_number = document.getElementById('bedheadNum').value;

        fetch('/patients', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ name, admit_date, nic, bedhead_number }),
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to add patient');
            return response.json();
        })
        .then(patient => {
            addRowToTable(patient);
            document.getElementById('addPatientModal').classList.remove('active');
            this.reset();
        })
        .catch(error => alert(error.message));
    });

    // Build a new table row from a patient object
    function addRowToTable(patient) {
        const tbody = document.getElementById('patientTableBody');
        const row = document.createElement('tr');
        row.dataset.id = patient.id;
        row.innerHTML = `
            <td>${tbody.children.length + 1}</td>
            <td>${patient.name}</td>
            <td>${patient.nic}</td>
            <td>${patient.bedhead_number}</td>
            <td>
                <div class="col-actions">
                    <button type="button" class="btn-edit">Edit</button>
                    <button type="button" class="btn-delete">Del</button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
        attachRowEvents(row);
    }

    // Delete a patient — real DELETE request
    function attachRowEvents(row) {
        const id = row.dataset.id;

        row.querySelector('.btn-delete').addEventListener('click', function () {
            if (!confirm('Delete this patient?')) return;

            fetch(`/patients/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken },
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed to delete');
                row.remove();
            })
            .catch(error => alert(error.message));
        });

        row.querySelector('.btn-edit').addEventListener('click', function () {
            const newName = prompt('Edit patient name:', row.children[1].textContent);
            if (newName === null) return; // cancelled

            fetch(`/patients/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ name: newName }),
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed to update');
                return response.json();
            })
            .then(patient => {
                row.children[1].textContent = patient.name;
            })
            .catch(error => alert(error.message));
        });
    }

    // Wire up buttons for rows that were rendered by Blade on page load
    document.querySelectorAll('#patientTableBody tr').forEach(attachRowEvents);
</script>

</body>
</html>