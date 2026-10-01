@extends('layouts.app')

@section('title', 'Ward Inventory - Injection')

@section('content')

<!-- Sidebar Panel -->
<div class="sidebar">
    <div class="sidebar-header">
        <i class="fa-solid fa-flask"></i>
        <div>
            <div class="sidebar-title">Ward Inventory</div>
            <div class="sidebar-subtitle">INJECTION</div>
        </div>
    </div>

    <!-- Medicine Search Form -->
    <form action="{{ route('injections.index') }}" method="GET" class="search-box">
        <i class="fa-solid fa-search"></i>
        <input type="text" name="search_medicine" value="{{ request('search_medicine') }}" placeholder="Search medicines..." onchange="this.form.submit()">
    </form>

    <!-- + Add Medicine Button -->
    <button class="btn-add-primary" onclick="openModal('addMedicineModal')">
        <i class="fa-solid fa-plus"></i> Add Medicine
    </button>

    <!-- Medicine List -->
    <div class="medicine-list">
        @forelse($medicines as $med)
            <div class="medicine-card {{ (isset($selectedMedicine) && $selectedMedicine->id == $med->id) ? 'active' : '' }}">
                <a href="{{ route('injections.index', ['medicine_id' => $med->id, 'search_medicine' => request('search_medicine')]) }}" style="text-decoration: none; color: inherit; flex: 1;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div class="medicine-name">{{ $med->name }}</div>
                            <div class="medicine-actions">
                                <i class="fa-solid fa-pen-to-square" title="Edit"></i>
                                <i class="fa-solid fa-lock" title="Lock"></i>
                            </div>
                        </div>
                        <span class="medicine-badge">{{ $med->available_stock }} {{ $med->unit->unit_name ?? 'ml' }}</span>
                    </div>
                </a>
                <form action="{{ route('medicines.destroy', $med->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this medicine?');" style="margin-left: 8px;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer;" title="Delete">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </form>
            </div>
        @empty
            <div style="text-align: center; color: var(--text-muted); padding: 20px; font-size: 13px;">
                No medicines found in this category. Click <strong>+ Add Medicine</strong> above to add one!
            </div>
        @endforelse
    </div>
</div>

<!-- Main Area -->
<div class="main-content">

    <!-- Selected Medicine Stock Hero Banner -->
    @if($selectedMedicine)
        <div class="hero-banner">
            <div>
                <div class="hero-banner-title">{{ $selectedMedicine->name }}</div>
                <div class="hero-banner-subtitle">Current Ward Stock Balance</div>
            </div>
            <div class="hero-stock-pill">
                <div class="hero-stock-value">{{ $selectedMedicine->available_stock }} {{ $selectedMedicine->unit->unit_name ?? 'ml' }}</div>
                <div class="hero-stock-label">AVAILABLE</div>
            </div>
        </div>
    @else
        <div class="hero-banner" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%);">
            <div>
                <div class="hero-banner-title">NO MEDICINE SELECTED</div>
                <div class="hero-banner-subtitle">Select a medicine from the left list or click + Add Medicine</div>
            </div>
        </div>
    @endif

    <!-- Filter & Search Controls -->
    <div class="filter-section">
        <div class="orders-count-tab">
            Pharmacy Orders ({{ $requisitionLogs->count() }})
        </div>

        <form action="{{ route('injections.index') }}" method="GET" class="filter-controls">
            @if(request('medicine_id'))
                <input type="hidden" name="medicine_id" value="{{ request('medicine_id') }}">
            @endif
            
            <div class="search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" name="search_records" value="{{ request('search_records') }}" class="filter-input" placeholder="Search records...">
            </div>

            <input type="date" name="date" value="{{ request('date') }}" class="filter-input" style="padding-left: 12px;">

            <button type="submit" class="btn-add-primary" style="padding: 8px 14px; font-size: 13px;">Filter</button>
            <a href="{{ route('injections.index') }}" class="btn-clear-filter">
                <i class="fa-solid fa-times"></i> Clear
            </a>
        </form>
    </div>

    <!-- Pharmacy Requisitions Log Table Card -->
    <div class="table-card">
        <div class="table-header-row">
            <h2 class="table-title">Pharmacy Requisitions Log</h2>
            <button class="btn-add-primary" onclick="openModal('addOrderModal')">
                <i class="fa-solid fa-plus"></i> Add Order
            </button>
        </div>

        <div style="overflow-x: auto;">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>DATE</th>
                        <th>DRUG NAME</th>
                        <th>REQUESTED QUANTITY</th>
                        <th>CONFIRM SIGNATURE FOR REQUEST</th>
                        <th>APPROVED OF MS</th>
                        <th>RECEIVED QUANTITY</th>
                        <th>SIGNATURE OF ISSUED OFFICER & DATE</th>
                        <th>CONFIRM SIGNATURE RECEIVED & DATE</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requisitionLogs as $log)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($log->order->date)->format('m/d/Y') }}</td>
                            <td style="font-weight: 700; color: var(--text-dark);">{{ $log->medicine->name ?? 'N/A' }}</td>
                            <td>{{ $log->qty_requested }} {{ $log->medicine->unit->unit_name ?? 'ml' }}</td>
                            <td>{{ $log->order->requester->name ?? 'Ward Nurse' }}</td>
                            <td>
                                <span class="status-badge {{ strtolower($log->order->ms_approval_status) }}">
                                    {{ $log->order->ms_approval_status }}
                                </span>
                            </td>
                            <td>{{ $log->qty_issued }}</td>
                            <td>{{ $log->order->approver->name ?? '-' }}</td>
                            <td>{{ $log->order->requester->name ?? 'Ward Nurse' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No requisition log entries found. Click <strong>+ Add Order</strong> to record a new requisition.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('modals')
    @include('components.add-medicine-modal', ['currentCategory' => $category])
    @include('components.add-order-modal')
@endsection
