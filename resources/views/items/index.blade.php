@extends('layouts.app')

@section('title', 'Stock Catalog - Ward Inventory Management System')

@section('content')
<!-- ITEM TYPE FILTER TABS -->
<div style="display: flex; gap: 10px; background: #fff; border-radius: 12px; padding: 10px; margin-bottom: 20px; border: 1px solid #e5e7eb;">
    <a href="{{ route('items.index') }}" class="btn {{ !request('type') ? 'btn-primary' : 'btn-secondary' }}">All Types</a>
    <a href="{{ route('items.index', ['type' => 'medicine']) }}" class="btn {{ request('type') == 'medicine' ? 'btn-primary' : 'btn-secondary' }}">Medicine</a>
    <a href="{{ route('items.index', ['type' => 'surgical']) }}" class="btn {{ request('type') == 'surgical' ? 'btn-primary' : 'btn-secondary' }}">Surgical</a>
    <a href="{{ route('items.index', ['type' => 'injection']) }}" class="btn {{ request('type') == 'injection' ? 'btn-primary' : 'btn-secondary' }}">Injection</a>
    
    <div style="margin-left: auto;">
        <button onclick="document.getElementById('addItemModal').style.display='block'" class="btn btn-success">+ Add New Item</button>
    </div>
</div>

<div style="display: flex; gap: 24px;">
    <!-- LEFT ITEM SIDEBAR -->
    <div style="width: 280px; background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; padding: 16px; height: fit-content;">
        <form action="{{ route('items.index') }}" method="GET" style="margin-bottom: 16px;">
            @if(request('type')) <input type="hidden" name="type" value="{{ request('type') }}"> @endif
            @if(request('subtype')) <input type="hidden" name="subtype" value="{{ request('subtype') }}"> @endif
            <input type="text" name="search" class="form-control" placeholder="Search medicines..." value="{{ request('search') }}">
        </form>

        <div style="max-height: 600px; overflow-y: auto;">
            @forelse($items as $item)
                @php
                    $qtyClass = 'qty--good';
                    if ($item->quantity == 0) $qtyClass = 'qty--danger';
                    elseif ($item->quantity < 15) $qtyClass = 'qty--warning';
                    $isActive = $selectedItem && $selectedItem->item_id == $item->item_id;
                @endphp
                <a href="{{ route('items.index', array_merge(request()->query(), ['selected_id' => $item->item_id])) }}" 
                   style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border-radius: 8px; text-decoration: none; margin-bottom: 4px; background: {{ $isActive ? '#eef2ff' : 'transparent' }}; border: 1px solid {{ $isActive ? '#c7d2fe' : 'transparent' }};">
                    <span style="font-size: 14px; font-weight: {{ $isActive ? '700' : '500' }}; color: {{ $isActive ? '#2b3fd6' : '#1f2937' }};">{{ $item->item_name }}</span>
                    <span class="badge {{ $qtyClass }}">{{ $item->quantity }}</span>
                </a>
            @empty
                <div style="padding: 12px; color: #9ca3af; text-align: center; font-size: 13px;">No items found.</div>
            @endforelse
        </div>
    </div>

    <!-- RIGHT ITEM DETAILS & TRANSACTIONS -->
    <div style="flex: 1;">
        @if($selectedItem)
            <!-- STOCK HEADER -->
            <div class="stock-header">
                <div>
                    <div class="stock-header__title">{{ $selectedItem->item_name }}</div>
                    <div class="stock-header__subtitle">
                        Category: <strong>{{ ucfirst($selectedItem->item_type) }}</strong> | 
                        Subtype: <strong>{{ $selectedItem->item_subtype }}</strong> | 
                        Workflow: <code>{{ $selectedItem->workflow_pattern }}</code>
                    </div>
                </div>
                <div class="stock-header__balance">
                    <span class="stock-header__qty">{{ $selectedItem->quantity }}</span>
                    <span class="stock-header__label">Stock Balance</span>
                </div>
            </div>

            <!-- ACTIVE TRANSACTIONS LOG -->
            <div class="card">
                <div class="card__header">
                    <div class="card__title">
                        @if(in_array($selectedItem->workflow_pattern, ['request_simple', 'request_approved']))
                            Pharmacy Requisitions Log ({{ $selectedItem->workflow_pattern }})
                        @elseif($selectedItem->workflow_pattern === 'narcotic_direct')
                            Narcotic Administration Log to Patients (narcotic_direct)
                        @else
                            Consumable Ward Issue Log (consumable_direct)
                        @endif
                    </div>
                </div>
                <div class="card__body" style="padding: 0;">
                    @if(in_array($selectedItem->workflow_pattern, ['request_simple', 'request_approved']))
                        <!-- REQUESTED STOCK TABLE -->
                        <table class="log-table">
                            <thead>
                                <tr>
                                    <th>Req ID</th>
                                    <th>Date</th>
                                    <th>Qty Requested</th>
                                    <th>Requested By</th>
                                    <th>Status</th>
                                    @if($selectedItem->workflow_pattern === 'request_approved')
                                        <th>MS Approval</th>
                                        <th>Issuing Officer</th>
                                    @endif
                                    <th>Qty Received</th>
                                    <th>Receiving Officer</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeTransactions as $tx)
                                    <tr>
                                        <td class="req-no">REQ-{{ $tx->request_id }}</td>
                                        <td>{{ $tx->request_date }}</td>
                                        <td>{{ $tx->required_quantity }}</td>
                                        <td>{{ $tx->requestedBy->name ?? '—' }}</td>
                                        <td><span class="badge badge--{{ $tx->status }}">{{ strtoupper($tx->status) }}</span></td>
                                        @if($selectedItem->workflow_pattern === 'request_approved')
                                            <td>{{ $tx->approvedByMs->name ?? '—' }}</td>
                                            <td>{{ $tx->issuedOfficer->name ?? '—' }}</td>
                                        @endif
                                        <td>{{ $tx->received_quantity ?? '—' }}</td>
                                        <td>{{ $tx->confirmReceivedBy->name ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" style="text-align: center; color: #9ca3af; padding: 20px;">No requisitions logged for this item.</td></tr>
                                @endforelse
                            </tbody>
                        </table>

                    @elseif($selectedItem->workflow_pattern === 'narcotic_direct')
                        <!-- NARCOTIC USAGE TABLE -->
                        <table class="log-table">
                            <thead>
                                <tr>
                                    <th>Usage ID</th>
                                    <th>Date & Time</th>
                                    <th>Patient Name</th>
                                    <th>NIC / BHT</th>
                                    <th>Bed No</th>
                                    <th>Dosage</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeTransactions as $tx)
                                    <tr>
                                        <td>#{{ $tx->usage_id }}</td>
                                        <td>{{ $tx->usage_date }} {{ $tx->usage_time }}</td>
                                        <td><strong>{{ $tx->patient->name ?? 'N/A' }}</strong></td>
                                        <td>{{ $tx->patient->nic ?? 'N/A' }} / {{ $tx->patient->bht ?? 'N/A' }}</td>
                                        <td>{{ $tx->bed_no ?? '—' }}</td>
                                        <td><span class="badge qty--warning">{{ $tx->dosage }}</span></td>
                                        <td>{{ $tx->recordedBy->name ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" style="text-align: center; color: #9ca3af; padding: 20px;">No narcotic usage logged for this item.</td></tr>
                                @endforelse
                            </tbody>
                        </table>

                    @elseif($selectedItem->workflow_pattern === 'consumable_direct')
                        <!-- CONSUMABLE USAGE TABLE -->
                        <table class="log-table">
                            <thead>
                                <tr>
                                    <th>Usage ID</th>
                                    <th>Date</th>
                                    <th>Bed Head No</th>
                                    <th>Qty Issued</th>
                                    <th>Stock Balance</th>
                                    <th>In-Charge Staff</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeTransactions as $tx)
                                    <tr>
                                        <td>#{{ $tx->usage_id }}</td>
                                        <td>{{ $tx->usage_date }}</td>
                                        <td>{{ $tx->bed_head_no ?? '—' }}</td>
                                        <td><strong>{{ $tx->quantity }}</strong></td>
                                        <td><span class="badge qty--good">{{ $tx->balance }}</span></td>
                                        <td>{{ $tx->inchargeStaff->name ?? '—' }}</td>
                                        <td>{{ $tx->notes ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" style="text-align: center; color: #9ca3af; padding: 20px;">No consumable usage logged for this item.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        @else
            <div class="card" style="padding: 40px; text-align: center; color: #6b7280;">Select an item from the sidebar to view details and history.</div>
        @endif
    </div>
</div>

<!-- ADD ITEM MODAL -->
<div id="addItemModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; padding: 40px;">
    <div style="max-width: 500px; margin: auto; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 700;">Add New Stock Item</h3>
            <button onclick="document.getElementById('addItemModal').style.display='none'" style="border: none; background: transparent; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('items.store') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Item Name</label>
                <input type="text" name="item_name" class="form-control" placeholder="e.g. Paracetamol Syrup" required>
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Item Type</label>
                <select name="item_type" class="form-control" required>
                    <option value="medicine">Medicine</option>
                    <option value="surgical">Surgical</option>
                    <option value="injection">Injection</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Item Subtype</label>
                <select name="item_subtype" class="form-control" required>
                    <option value="narcotic">narcotic</option>
                    <option value="syrup">syrup</option>
                    <option value="iv_fluid">iv_fluid</option>
                    <option value="oral_countable">oral_countable</option>
                    <option value="oral_antibiotic">oral_antibiotic</option>
                    <option value="bulk_medicine">bulk_medicine</option>
                    <option value="surgical_consumable_1">surgical_consumable_1</option>
                    <option value="surgical_consumable_2">surgical_consumable_2</option>
                    <option value="local_purchase">local_purchase</option>
                    <option value="injection">injection</option>
                    <option value="injection_antibiotic">injection_antibiotic</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label>Workflow Pattern</label>
                <select name="workflow_pattern" class="form-control" required>
                    <option value="request_simple">request_simple (Standard Requisition)</option>
                    <option value="request_approved">request_approved (Requires MS Officer & Issuing Officer)</option>
                    <option value="narcotic_direct">narcotic_direct (Patient Direct Log)</option>
                    <option value="consumable_direct">consumable_direct (Ward Consumable Log)</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label>Initial Stock Quantity</label>
                <input type="number" name="quantity" class="form-control" value="0" min="0" required>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('addItemModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Item</button>
            </div>
        </form>
    </div>
</div>
@endsection
