@extends('layouts.app')

@section('title', 'Consumable Usage Log - Ward Inventory Management System')

@section('content')
<div class="stock-header">
    <div>
        <div class="stock-header__title">Ward Consumable Direct Usage Log</div>
        <div class="stock-header__subtitle">Log direct issue of surgical consumables and local purchase stock to ward beds</div>
    </div>
    <button onclick="document.getElementById('addConsumableModal').style.display='block'" class="btn btn-primary">+ Log Consumable Issue</button>
</div>

<div class="card">
    <div class="card__header">
        <div class="card__title">Consumable Usage Log Records</div>
    </div>
    <div class="card__body" style="padding: 0; overflow-x: auto;">
        <table class="log-table">
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Date</th>
                    <th>Item Name</th>
                    <th>Bed Head Ticket (BHT)</th>
                    <th>Qty Issued</th>
                    <th>Remaining Stock Balance</th>
                    <th>In-Charge Officer</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usages as $usage)
                    <tr>
                        <td>#{{ $usage->usage_id }}</td>
                        <td>{{ $usage->usage_date }}</td>
                        <td><strong>{{ $usage->item->item_name ?? 'N/A' }}</strong></td>
                        <td>{{ $usage->bed_head_no ?? '—' }}</td>
                        <td><strong>{{ $usage->quantity }} units</strong></td>
                        <td><span class="badge qty--good">{{ $usage->balance }} units</span></td>
                        <td>{{ $usage->inchargeStaff->name ?? '—' }}</td>
                        <td>{{ $usage->notes ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align: center; color: #9ca3af; padding: 24px;">No consumable usage logged.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ADD CONSUMABLE USAGE MODAL -->
<div id="addConsumableModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; padding: 40px;">
    <div style="max-width: 500px; margin: auto; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 700;">Log Consumable Issue</h3>
            <button onclick="document.getElementById('addConsumableModal').style.display='none'" style="border: none; background: transparent; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('consumable-usage.store') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Select Consumable Item</label>
                <select name="item_id" class="form-control" required>
                    <option value="">-- Choose Consumable Item --</option>
                    @foreach($consumableItems as $itm)
                        <option value="{{ $itm->item_id }}">{{ $itm->item_name }} (Stock: {{ $itm->quantity }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Bed Head Ticket / No. (BHT)</label>
                <input type="text" name="bed_head_no" class="form-control" placeholder="e.g. BHT-48-102">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                <div class="form-group">
                    <label>Usage Date</label>
                    <input type="date" name="usage_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label>Quantity Issued</label>
                    <input type="number" name="quantity" class="form-control" min="1" placeholder="e.g. 5" required>
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label>In-Charge Officer (Staff Signature)</label>
                <select name="incharge_staff_id" class="form-control" required>
                    <option value="">-- Select Staff Member --</option>
                    @foreach($staffList as $st)
                        <option value="{{ $st->staff_id }}">{{ $st->name }} ({{ $st->role }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label>Notes / Comments</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Used for dressing wound at Bed 4"></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('addConsumableModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Issue Log</button>
            </div>
        </form>
    </div>
</div>
@endsection
