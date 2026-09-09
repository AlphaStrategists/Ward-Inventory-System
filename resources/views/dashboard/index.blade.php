@extends('layouts.app')

@section('title', 'Dashboard - Ward Inventory Management System')

@section('content')
<div class="stock-header">
    <div>
        <div class="stock-header__title">Ward Inventory Dashboard</div>
        <div class="stock-header__subtitle">Real-time overview, low stock alerts, and pending MS officer approvals</div>
    </div>
    <div class="stock-header__balance">
        <span class="stock-header__qty">{{ $stats['low_stock_count'] }} Items</span>
        <span class="stock-header__label">Low Stock Alerts</span>
    </div>
</div>

<!-- STATS GRID -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <div class="card" style="margin-bottom: 0; padding: 20px;">
        <div style="font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase;">Total Items Registered</div>
        <div style="font-size: 28px; font-weight: 800; color: #2b3fd6; margin-top: 6px;">{{ $stats['total_items'] }}</div>
    </div>
    <div class="card" style="margin-bottom: 0; padding: 20px;">
        <div style="font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase;">Low Stock Items</div>
        <div style="font-size: 28px; font-weight: 800; color: #dc2626; margin-top: 6px;">{{ $stats['low_stock_count'] }}</div>
    </div>
    <div class="card" style="margin-bottom: 0; padding: 20px;">
        <div style="font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase;">Pending MS Approvals</div>
        <div style="font-size: 28px; font-weight: 800; color: #d97706; margin-top: 6px;">{{ $stats['pending_approvals_count'] }}</div>
    </div>
    <div class="card" style="margin-bottom: 0; padding: 20px;">
        <div style="font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase;">Narcotic Patients</div>
        <div style="font-size: 28px; font-weight: 800; color: #16a34a; margin-top: 6px;">{{ $stats['total_patients'] }}</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- LOW STOCK ALERTS CARD -->
    <div class="card">
        <div class="card__header">
            <div class="card__title">⚠️ Low-Stock Alerts</div>
            <a href="{{ route('items.index') }}" class="btn btn-secondary">View All Items</a>
        </div>
        <div class="card__body" style="padding: 0;">
            @if($lowStockItems->isEmpty())
                <div style="padding: 20px; color: #6b7280; text-align: center;">No items currently below low stock threshold.</div>
            @else
                <table class="log-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Type / Subtype</th>
                            <th>Workflow Pattern</th>
                            <th>Current Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockItems as $item)
                            <tr>
                                <td><strong>{{ $item->item_name }}</strong></td>
                                <td>{{ ucfirst($item->item_type) }} ({{ $item->item_subtype }})</td>
                                <td><code style="font-size: 11px; background: #eee; padding: 2px 6px; border-radius: 4px;">{{ $item->workflow_pattern }}</code></td>
                                <td><span class="badge qty--danger">{{ $item->quantity }} units</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <!-- PENDING MS APPROVALS CARD -->
    <div class="card">
        <div class="card__header">
            <div class="card__title">📋 Pending MS Officer Approvals</div>
            <a href="{{ route('requested-stock.index') }}" class="btn btn-secondary">View All Requests</a>
        </div>
        <div class="card__body" style="padding: 0;">
            @if($pendingMsApprovals->isEmpty())
                <div style="padding: 20px; color: #6b7280; text-align: center;">No pending requests requiring MS officer sign-off.</div>
            @else
                <table class="log-table">
                    <thead>
                        <tr>
                            <th>Date / Req ID</th>
                            <th>Item</th>
                            <th>Qty Req.</th>
                            <th>Requested By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingMsApprovals as $req)
                            <tr>
                                <td>
                                    <strong>REQ-{{ $req->request_id }}</strong><br>
                                    <span style="font-size: 11px; color: #6b7280;">{{ $req->request_date }}</span>
                                </td>
                                <td>{{ $req->item->item_name }}</td>
                                <td><strong>{{ $req->required_quantity }}</strong></td>
                                <td>{{ $req->requestedBy->name ?? 'Staff #'.$req->requested_by_staff_id }}</td>
                                <td>
                                    <form action="{{ route('requested-stock.approve-ms', $req->request_id) }}" method="POST" style="display: flex; gap: 4px;">
                                        @csrf
                                        <select name="approved_by_ms_staff_id" class="form-control" style="font-size: 11px; padding: 4px;" required>
                                            <option value="">Select MS Officer...</option>
                                            @foreach($stats['ms_staff'] as $ms)
                                                <option value="{{ $ms->staff_id }}">{{ $ms->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-success" style="padding: 4px 8px; font-size: 11px;">Approve</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

<!-- RECENT REQUISITIONS -->
<div class="card">
    <div class="card__header">
        <div class="card__title">Recent Stock Requisitions</div>
        <a href="{{ route('requested-stock.index') }}" class="btn btn-primary">+ New Requisition</a>
    </div>
    <div class="card__body" style="padding: 0;">
        <table class="log-table">
            <thead>
                <tr>
                    <th>Req. No.</th>
                    <th>Date</th>
                    <th>Item Name</th>
                    <th>Pattern</th>
                    <th>Qty Req.</th>
                    <th>Requested By</th>
                    <th>Status</th>
                    <th>MS Approval</th>
                    <th>Issuing Officer</th>
                    <th>Receiving Officer</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentRequests as $req)
                    <tr>
                        <td class="req-no">REQ-{{ $req->request_id }}</td>
                        <td>{{ $req->request_date }}</td>
                        <td><strong>{{ $req->item->item_name ?? 'N/A' }}</strong></td>
                        <td><code style="font-size: 11px; background: #eee; padding: 2px 6px; border-radius: 4px;">{{ $req->item->workflow_pattern ?? 'N/A' }}</code></td>
                        <td>{{ $req->required_quantity }}</td>
                        <td>{{ $req->requestedBy->name ?? 'N/A' }}</td>
                        <td><span class="badge badge--{{ $req->status }}">{{ strtoupper($req->status) }}</span></td>
                        <td>{{ $req->approvedByMs->name ?? '—' }}</td>
                        <td>{{ $req->issuedOfficer->name ?? '—' }}</td>
                        <td>{{ $req->confirmReceivedBy->name ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
