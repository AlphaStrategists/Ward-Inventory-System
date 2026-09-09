@extends('layouts.app')

@section('title', 'General Inventory - Ward Inventory Management System')

@section('content')
<div class="stock-header">
    <div>
        <div class="stock-header__title">General Non-Medical Inventory Log</div>
        <div class="stock-header__subtitle">Tracking linen, bedsheets, and ward supplies — standalone stock without staff requester links</div>
    </div>
    <button onclick="document.getElementById('addGenModal').style.display='block'" class="btn btn-primary">+ Add Inventory Entry</button>
</div>

<div class="card">
    <div class="card__header">
        <div class="card__title">Non-Medical Stock Records</div>
        <form action="{{ route('general-inventory.index') }}" method="GET" style="display: flex; gap: 8px;">
            <input type="text" name="search" class="form-control" placeholder="Search item or code..." value="{{ request('search') }}" style="width: 200px; padding: 6px 12px; font-size: 13px;">
            <button type="submit" class="btn btn-secondary" style="padding: 6px 12px; font-size: 13px;">Search</button>
        </form>
    </div>
    <div class="card__body" style="padding: 0; overflow-x: auto;">
        <table class="log-table">
            <thead>
                <tr>
                    <th>Inv. ID</th>
                    <th>Entry Date</th>
                    <th>Item Name</th>
                    <th>Item Code</th>
                    <th>Total Received</th>
                    <th>Total Issued</th>
                    <th>Current Balance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventories as $inv)
                    <tr>
                        <td>#{{ $inv->inventory_id }}</td>
                        <td>{{ $inv->entry_date }}</td>
                        <td><strong>{{ $inv->item_name }}</strong></td>
                        <td><code>{{ $inv->item_code ?? '—' }}</code></td>
                        <td><span style="color: #16a34a; font-weight: 700;">+{{ $inv->received }}</span></td>
                        <td><span style="color: #dc2626; font-weight: 700;">-{{ $inv->issued }}</span></td>
                        <td><span class="badge qty--good">{{ $inv->balance }} units</span></td>
                        <td>
                            <button onclick="openEditGenModal('{{ $inv->inventory_id }}', '{{ addslashes($inv->item_name) }}', '{{ addslashes($inv->item_code) }}', '{{ $inv->entry_date }}', '{{ $inv->received }}', '{{ $inv->issued }}')" class="btn btn-secondary" style="padding: 4px 8px; font-size: 11px;">Edit Stock</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align: center; color: #9ca3af; padding: 24px;">No general non-medical inventory recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ADD GENERAL INVENTORY MODAL -->
<div id="addGenModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; padding: 40px;">
    <div style="max-width: 500px; margin: auto; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 700;">Add General Inventory Entry</h3>
            <button onclick="document.getElementById('addGenModal').style.display='none'" style="border: none; background: transparent; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('general-inventory.store') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Item Name</label>
                <input type="text" name="item_name" class="form-control" placeholder="e.g. Ward Bed Sheets" required>
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Item Code</label>
                <input type="text" name="item_code" class="form-control" placeholder="e.g. LINEN-001">
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Entry Date</label>
                <input type="date" name="entry_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
                <div class="form-group">
                    <label>Quantity Received</label>
                    <input type="number" name="received" class="form-control" value="0" min="0" required>
                </div>
                <div class="form-group">
                    <label>Quantity Issued</label>
                    <input type="number" name="issued" class="form-control" value="0" min="0" required>
                </div>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('addGenModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Entry</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT GENERAL INVENTORY MODAL -->
<div id="editGenModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; padding: 40px;">
    <div style="max-width: 500px; margin: auto; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 700;">Update General Inventory Entry</h3>
            <button onclick="document.getElementById('editGenModal').style.display='none'" style="border: none; background: transparent; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form id="editGenForm" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Item Name</label>
                <input type="text" id="editItemName" name="item_name" class="form-control" required>
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Item Code</label>
                <input type="text" id="editItemCode" name="item_code" class="form-control">
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Entry Date</label>
                <input type="date" id="editEntryDate" name="entry_date" class="form-control" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
                <div class="form-group">
                    <label>Quantity Received</label>
                    <input type="number" id="editReceived" name="received" class="form-control" min="0" required>
                </div>
                <div class="form-group">
                    <label>Quantity Issued</label>
                    <input type="number" id="editIssued" name="issued" class="form-control" min="0" required>
                </div>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('editGenModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Entry</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditGenModal(id, name, code, date, received, issued) {
        document.getElementById('editGenForm').action = '/general-inventory/' + id;
        document.getElementById('editItemName').value = name;
        document.getElementById('editItemCode').value = code;
        document.getElementById('editEntryDate').value = date;
        document.getElementById('editReceived').value = received;
        document.getElementById('editIssued').value = issued;
        document.getElementById('editGenModal').style.display = 'block';
    }
</script>
@endsection
