@extends('layouts.app')

@section('title', 'Narcotic Usage Log - Ward Inventory Management System')

@section('content')
<div class="stock-header">
    <div>
        <div class="stock-header__title">Narcotic Medicine Administration Log</div>
        <div class="stock-header__subtitle">Direct administration tracking linked to patient records and bed numbers</div>
    </div>
    <button onclick="document.getElementById('addNarcoticModal').style.display='block'" class="btn btn-danger">+ Log Narcotic Administration</button>
</div>

<div class="card">
    <div class="card__header">
        <div class="card__title">Narcotic Administration Records</div>
    </div>
    <div class="card__body" style="padding: 0; overflow-x: auto;">
        <table class="log-table">
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Date & Time</th>
                    <th>Narcotic Medicine</th>
                    <th>Patient Name</th>
                    <th>NIC / BHT</th>
                    <th>Bed No</th>
                    <th>Dosage</th>
                    <th>Recorded By (Staff)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usages as $usage)
                    <tr>
                        <td>#{{ $usage->usage_id }}</td>
                        <td>{{ $usage->usage_date }} at {{ $usage->usage_time }}</td>
                        <td><strong>{{ $usage->item->item_name ?? 'N/A' }}</strong></td>
                        <td><strong>{{ $usage->patient->name ?? 'N/A' }}</strong></td>
                        <td>
                            NIC: {{ $usage->patient->nic ?? '—' }}<br>
                            <span style="font-size: 11px; color: #6b7280;">BHT: {{ $usage->patient->bht ?? '—' }}</span>
                        </td>
                        <td>{{ $usage->bed_no ?? '—' }}</td>
                        <td><span class="badge qty--warning">{{ $usage->dosage }}</span></td>
                        <td>{{ $usage->recordedBy->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align: center; color: #9ca3af; padding: 24px;">No narcotic usage recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ADD NARCOTIC USAGE MODAL -->
<div id="addNarcoticModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; padding: 40px; overflow-y: auto;">
    <div style="max-width: 520px; margin: auto; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 700;">Log Narcotic Administration</h3>
            <button onclick="document.getElementById('addNarcoticModal').style.display='none'" style="border: none; background: transparent; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('narcotic-usage.store') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Select Narcotic Item</label>
                <select name="item_id" class="form-control" required>
                    <option value="">-- Choose Narcotic Medicine --</option>
                    @foreach($narcoticItems as $itm)
                        <option value="{{ $itm->item_id }}">{{ $itm->item_name }} (Stock: {{ $itm->quantity }})</option>
                    @endforeach
                </select>
            </div>

            <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px; margin-bottom: 14px; background: #fafafa;">
                <label style="font-size: 12px; font-weight: 700; color: #2b3fd6; text-transform: uppercase;">Patient Information</label>
                
                <div class="form-group" style="margin-top: 10px; margin-bottom: 10px;">
                    <label>Select Existing Patient</label>
                    <select name="patient_id" id="patientSelect" class="form-control" onchange="togglePatientFields()">
                        <option value="">-- Or Register New Patient Below --</option>
                        @foreach($patients as $pt)
                            <option value="{{ $pt->patient_id }}">{{ $pt->name }} (NIC: {{ $pt->nic ?? 'N/A' }}, BHT: {{ $pt->bht ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>

                <div id="newPatientFields">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label>Patient Full Name</label>
                        <input type="text" name="patient_name" class="form-control" placeholder="e.g. K. L. Perera">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div class="form-group">
                            <label>NIC Number</label>
                            <input type="text" name="patient_nic" class="form-control" placeholder="e.g. 198512345678">
                        </div>
                        <div class="form-group">
                            <label>Bed Head Ticket (BHT)</label>
                            <input type="text" name="patient_bht" class="form-control" placeholder="e.g. BHT-47-092">
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                <div class="form-group">
                    <label>Bed Number</label>
                    <input type="text" name="bed_no" class="form-control" placeholder="Bed 12" required>
                </div>
                <div class="form-group">
                    <label>Dosage</label>
                    <input type="text" name="dosage" class="form-control" placeholder="e.g. 50 mcg" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                <div class="form-group">
                    <label>Usage Date</label>
                    <input type="date" name="usage_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label>Usage Time</label>
                    <input type="time" name="usage_time" class="form-control" value="{{ date('H:i') }}" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label>Recorded By (Staff Signature)</label>
                <select name="recorded_by_staff_id" class="form-control" required>
                    <option value="">-- Select Staff Member --</option>
                    @foreach($staffList as $st)
                        <option value="{{ $st->staff_id }}">{{ $st->name }} ({{ $st->role }})</option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('addNarcoticModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-danger">Log Administration</button>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePatientFields() {
        const pSel = document.getElementById('patientSelect');
        const newFields = document.getElementById('newPatientFields');
        if (pSel.value) {
            newFields.style.opacity = '0.5';
        } else {
            newFields.style.opacity = '1';
        }
    }
</script>
@endsection
