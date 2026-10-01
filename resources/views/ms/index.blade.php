@extends('layouts.app')

@section('title', 'Medical Superintendent (MS) Approval Portal')

@section('content')

<div class="main-content" style="max-width: 1400px; margin: 0 auto; width: 100%;">

    <!-- MS Header Portal Hero Banner -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 16px; padding: 28px 32px; color: #ffffff; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 6px;">
                <i class="fa-solid fa-user-shield" style="font-size: 28px; color: #38bdf8;"></i>
                <h1 style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; margin: 0;">MS Approval Portal</h1>
            </div>
            <p style="font-size: 14px; opacity: 0.85; margin: 0;">Medical Superintendent Dashboard — Review and update ward requisition statuses.</p>
        </div>

        <!-- Metric Badges -->
        <div style="display: flex; gap: 16px; flex-wrap: wrap;">
            <div style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px); padding: 12px 20px; border-radius: 12px; text-align: center; border: 1px solid rgba(255, 255, 255, 0.15);">
                <div style="font-size: 20px; font-weight: 800; color: #ffffff;">{{ $totalCount }}</div>
                <div style="font-size: 11px; font-weight: 700; opacity: 0.8; text-transform: uppercase;">Total Orders</div>
            </div>
            <div style="background: rgba(245, 158, 11, 0.2); backdrop-filter: blur(8px); padding: 12px 20px; border-radius: 12px; text-align: center; border: 1px solid rgba(245, 158, 11, 0.3);">
                <div style="font-size: 20px; font-weight: 800; color: #fbbf24;">{{ $pendingCount }}</div>
                <div style="font-size: 11px; font-weight: 700; color: #fef3c7; text-transform: uppercase;">Pending</div>
            </div>
            <div style="background: rgba(34, 197, 94, 0.2); backdrop-filter: blur(8px); padding: 12px 20px; border-radius: 12px; text-align: center; border: 1px solid rgba(34, 197, 94, 0.3);">
                <div style="font-size: 20px; font-weight: 800; color: #4ade80;">{{ $completedCount }}</div>
                <div style="font-size: 11px; font-weight: 700; color: #dcfce7; text-transform: uppercase;">Completed</div>
            </div>
            <div style="background: rgba(56, 189, 248, 0.2); backdrop-filter: blur(8px); padding: 12px 20px; border-radius: 12px; text-align: center; border: 1px solid rgba(56, 189, 248, 0.3);">
                <div style="font-size: 20px; font-weight: 800; color: #38bdf8;">{{ $approvedCount }}</div>
                <div style="font-size: 11px; font-weight: 700; color: #e0f2fe; text-transform: uppercase;">Approved</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="filter-section" style="margin-top: 20px;">
        <div class="orders-count-tab">
            <i class="fa-solid fa-list-check" style="margin-right: 6px;"></i> Orders List ({{ $requisitionLogs->count() }})
        </div>

        <form action="{{ route('ms.index') }}" method="GET" class="filter-controls" style="flex-wrap: wrap; gap: 10px;">
            <div class="search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" name="search_records" value="{{ request('search_records') }}" class="filter-input" placeholder="Search REQ No, drug or requester...">
            </div>

            <select name="status" class="filter-input" style="padding-left: 12px; cursor: pointer;">
                <option value="">-- All Statuses --</option>
                <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Approved</option>
                <option value="Rejected" {{ request('status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
            </select>

            <select name="category_id" class="filter-input" style="padding-left: 12px; cursor: pointer;">
                <option value="">-- All Categories --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <input type="date" name="date" value="{{ request('date') }}" class="filter-input" style="padding-left: 12px;">

            <button type="submit" class="btn-add-primary" style="padding: 8px 16px; font-size: 13px;">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            
            <a href="{{ route('ms.index') }}" class="btn-clear-filter">
                <i class="fa-solid fa-times"></i> Clear
            </a>
        </form>
    </div>

    <!-- Requisitions Log Table Card (No Add Medicine / No Add Order buttons as requested) -->
    <div class="table-card" style="margin-top: 20px;">
        <div class="table-header-row">
            <div>
                <h2 class="table-title"><i class="fa-solid fa-clipboard-list" style="color: var(--primary-blue); margin-right: 8px;"></i> Ward Requisition Orders List</h2>
                <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Select and update the MS Approval Status dropdown for any order below. Changes take effect instantly across all ward inventory views.</p>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>REQ NO / DATE</th>
                        <th>CATEGORY & DRUG NAME</th>
                        <th>WARD</th>
                        <th>REQUESTED QTY</th>
                        <th>REQUESTED BY</th>
                        <th style="min-width: 160px; text-align: center;">MS APPROVAL STATUS (EDIT)</th>
                        <th>APPROVED BY (MS)</th>
                        <th>REMARK / NOTES</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requisitionLogs as $log)
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: var(--primary-blue);">{{ $log->order->req_no ?? 'REQ-N/A' }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ \Carbon\Carbon::parse($log->order->date)->format('m/d/Y h:i A') }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-dark); text-transform: uppercase;">{{ $log->medicine->name ?? 'N/A' }}</div>
                                <span style="font-size: 11px; background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 10px; font-weight: 600;">
                                    {{ $log->medicine->category->name ?? 'General' }}
                                </span>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: #334155;">{{ $log->order->ward->ward_name ?? 'Ward 48' }}</span>
                            </td>
                            <td style="font-weight: 700;">
                                {{ $log->qty_requested }} {{ $log->medicine->unit->unit_name ?? 'units' }}
                            </td>
                            <td>{{ $log->order->requester->name ?? 'Ward Nurse' }}</td>
                            <td style="text-align: center;">
                                <!-- Interactive Dropdown Button to Edit Status -->
                                <form action="{{ route('orders.updateStatus', $log->order->id) }}" method="POST" style="margin: 0; display: inline-block;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="ms_approval_status" onchange="this.form.submit()" class="status-select {{ strtolower($log->order->ms_approval_status) }}" title="Click to manually update order status">
                                        <option value="Pending" {{ $log->order->ms_approval_status === 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="Completed" {{ $log->order->ms_approval_status === 'Completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="Approved" {{ $log->order->ms_approval_status === 'Approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="Rejected" {{ $log->order->ms_approval_status === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <span style="font-size: 12px; color: var(--text-dark); font-weight: 600;">
                                    {{ $log->order->approver->name ?? 'Medical Superintendent' }}
                                </span>
                            </td>
                            <td style="font-size: 12px; color: var(--text-muted);">
                                {{ $log->order->remark ?? ($log->remark ?? 'Standard Requisition') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                <i class="fa-solid fa-folder-open" style="font-size: 32px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                                No requisition orders found matching the filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
