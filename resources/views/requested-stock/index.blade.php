@extends('layouts.app')

@section('title', 'Stock Requisitions - Ward Inventory Management System')

@section('content')
<div class="stock-header">
    <div>
        <div class="stock-header__title">Pharmacy Requisitions Log</div>
        <div class="stock-header__subtitle">Manage stock orders for request_simple and request_approved items</div>
    </div>
    <div style="display: flex; gap: 10px;">
        <button onclick="document.getElementById('addRequestModal').style.display='block'" class="btn btn-warning">+ New Stock Requisition</button>
    </div>
</div>

<!-- REQUISITIONS TABLE -->
<div class="card">
    <div class="card__header">
        <div class="card__title">Requisitions History & Approvals</div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('requested-stock.index') }}" class="btn {{ !request('status') ? 'btn-primary' : 'btn-secondary' }}" style="padding: 4px 10px; font-size: 11px;">All</a>
            <a href="{{ route('requested-stock.index', ['status' => 'pending']) }}" class="btn {{ request('status') === 'pending' ? 'btn-primary' : 'btn-secondary' }}" style="padding: 4px 10px; font-size: 11px;">Pending</a>
            <a href="{{ route('requested-stock.index', ['status' => 'approved']) }}" class="btn {{ request('status') === 'approved' ? 'btn-primary' : 'btn-secondary' }}" style="padding: 4px 10px; font-size: 11px;">Approved</a>
            <a href="{{ route('requested-stock.index', ['status' => 'issued']) }}" class="btn {{ request('status') === 'issued' ? 'btn-primary' : 'btn-secondary' }}" style="padding: 4px 10px; font-size: 11px;">Issued</a>
            <a href="{{ route('requested-stock.index', ['status' => 'completed']) }}" class="btn {{ request('status') === 'completed' ? 'btn-primary' : 'btn-secondary' }}" style="padding: 4px 10px; font-size: 11px;">Completed</a>
        </div>
    </div>
    <div class="card__body" style="padding: 0; overflow-x: auto;">
        <table class="log-table">
            <thead>
                <tr>
                    <th>Req. No.</th>
                    <th>Date</th>
                    <th>Name of Item</th>
                    <th>Workflow</th>
                    <th>Qty Req.</th>
                    <th>Requested By</th>
                    <th>Status</th>
                    <th>MS Officer Approval</th>
                    <th>Issuing Officer & Date</th>
                    <th>Received Qty & Officer</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    <tr>
                        <td class="req-no">REQ-{{ $req->request_id }}</td>
                        <td>{{ $req->request_date }}</td>
                        <td>
                            <strong>{{ $req->item->item_name ?? 'Item #'.$req->item_id }}</strong><br>
                            <span style="font-size: 11px; color: #6b7280;">Bal Before: {{ $req->balance_before ?? 0 }}</span>
                        </td>
                        <td><code style="font-size: 11px; background: #eee; padding: 2px 6px; border-radius: 4px;">{{ $req->item->workflow_pattern }}</code></td>
                        <td><strong>{{ $req->required_quantity }}</strong></td>
                        <td>{{ $req->requestedBy->name ?? '—' }}</td>
                        <td><span class="badge badge--{{ $req->status }}">{{ strtoupper($req->status) }}</span></td>

                        <!-- MS Approval -->
                        <td>
                            @if($req->item->workflow_pattern === 'request_approved')
                                @if($req->approvedByMs)
                                    <span class="badge badge--approved">Approved</span><br>
                                    <span style="font-size: 11px;">{{ $req->approvedByMs->name }}</span>
                                @else
                                    <form action="{{ route('requested-stock.approve-ms', $req->request_id) }}" method="POST" style="display: flex; gap: 4px;">
                                        @csrf
                                        <select name="approved_by_ms_staff_id" class="form-control" style="font-size: 11px; padding: 4px;" required>
                                            <option value="">Select MS...</option>
                                            @foreach($msStaffList as $ms)
                                                <option value="{{ $ms->staff_id }}">{{ $ms->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-success" style="padding: 4px 8px; font-size: 11px;">Sign</button>
                                    </form>
                                @endif
                            @else
                                <span style="color: #9ca3af; font-size: 11px;">N/A (Simple)</span>
                            @endif
                        </td>

                        <!-- Issuing Officer -->
                        <td>
                            @if($req->item->workflow_pattern === 'request_approved')
                                @if($req->issuedOfficer)
                                    <strong>{{ $req->issuedOfficer->name }}</strong><br>
                                    <span style="font-size: 11px; color: #6b7280;">{{ $req->issued_date }}</span>
                                @elseif($req->status === 'approved')
                                    <form action="{{ route('requested-stock.issue', $req->request_id) }}" method="POST" style="display: flex; flex-direction: column; gap: 4px;">
                                        @csrf
                                        <select name="issued_officer_staff_id" class="form-control" style="font-size: 11px; padding: 4px;" required>
                                            <option value="">Issuing Officer...</option>
                                            @foreach($staffList as $st)
                                                <option value="{{ $st->staff_id }}">{{ $st->name }} ({{ $st->role }})</option>
                                            @endforeach
                                        </select>
                                        <input type="date" name="issued_date" class="form-control" style="font-size: 11px; padding: 4px;" value="{{ date('Y-m-d') }}" required>
                                        <button type="submit" class="btn btn-primary" style="padding: 4px 8px; font-size: 11px;">Sign Issue</button>
                                    </form>
                                @else
                                    <span style="color: #9ca3af; font-size: 11px;">Await MS</span>
                                @endif
                            @else
                                <span style="color: #9ca3af; font-size: 11px;">N/A (Simple)</span>
                            @endif
                        </td>

                        <!-- Received Qty & Officer -->
                        <td>
                            @if($req->status === 'completed')
                                <strong>{{ $req->received_quantity }} units</strong><br>
                                <span style="font-size: 11px; color: #6b7280;">by {{ $req->confirmReceivedBy->name ?? '—' }} on {{ $req->received_date }}</span>
                            @else
                                @php
                                    $canReceive = ($req->item->workflow_pattern === 'request_simple') || ($req->status === 'issued');
                                @endphp
                                @if($canReceive)
                                    <button onclick="openReceiveModal('{{ $req->request_id }}', '{{ $req->item->item_name }}', '{{ $req->required_quantity }}')" class="btn btn-success" style="padding: 4px 8px; font-size: 11px;">+ Confirm Receive</button>
                                @else
                                    <span style="color: #9ca3af; font-size: 11px;">Await Issue</span>
                                @endif
                            @endif
                        </td>

                        <td>
                            <span style="font-size: 11px; color: #6b7280;">ID #{{ $req->request_id }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" style="text-align: center; color: #9ca3af; padding: 24px;">No stock requisitions recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- CREATE REQUEST MODAL -->
<div id="addRequestModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; padding: 40px;">
    <div style="max-width: 500px; margin: auto; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 700;">New Pharmacy Stock Requisition</h3>
            <button onclick="document.getElementById('addRequestModal').style.display='none'" style="border: none; background: transparent; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('requested-stock.store') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Select Item to Request</label>
                <select name="item_id" class="form-control" required>
                    <option value="">-- Choose Item --</option>
                    @foreach($items as $itm)
                        <option value="{{ $itm->item_id }}">{{ $itm->item_name }} (Workflow: {{ $itm->workflow_pattern }}, Stock: {{ $itm->quantity }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Request Date</label>
                <input type="date" name="request_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Required Quantity</label>
                <input type="number" name="required_quantity" class="form-control" min="1" placeholder="e.g. 50" required>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label>Requested By (Staff Signature)</label>
                <select name="requested_by_staff_id" class="form-control" required>
                    <option value="">-- Select Staff Member --</option>
                    @foreach($staffList as $st)
                        <option value="{{ $st->staff_id }}">{{ $st->name }} ({{ $st->role }})</option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('addRequestModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-warning">Submit Requisition</button>
            </div>
        </form>
    </div>
</div>

<!-- RECEIVE STOCK MODAL -->
<div id="receiveModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; padding: 40px;">
    <div style="max-width: 480px; margin: auto; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 700;">Confirm Received Stock</h3>
            <button onclick="document.getElementById('receiveModal').style.display='none'" style="border: none; background: transparent; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form id="receiveForm" action="" method="POST">
            @csrf
            <div style="margin-bottom: 14px; font-size: 14px;">
                Item: <strong id="receiveItemName"></strong>
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Received Quantity</label>
                <input type="number" id="receivedQuantityInput" name="received_quantity" class="form-control" min="1" required>
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Received Date</label>
                <input type="date" name="received_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label>Receiving Officer (Staff Signature)</label>
                <select name="confirm_received_staff_id" class="form-control" required>
                    <option value="">-- Select Receiving Officer --</option>
                    @foreach($staffList as $st)
                        <option value="{{ $st->staff_id }}">{{ $st->name }} ({{ $st->role }})</option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('receiveModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-success">Confirm & Update Stock</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReceiveModal(reqId, itemName, reqQty) {
        document.getElementById('receiveForm').action = '/requested-stock/' + reqId + '/receive';
        document.getElementById('receiveItemName').innerText = itemName;
        document.getElementById('receivedQuantityInput').value = reqQty;
        document.getElementById('receiveModal').style.display = 'block';
    }
</script>
@endsection
