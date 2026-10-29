@extends('layouts.app')

@section('title', 'Pharmacy Orders & Requisitions')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
<div class="d-flex flex-column flex-grow-1 overflow-hidden bg-slate-50">

    <!-- Top Action & Filter Header -->
    <div class="bg-white border-bottom px-4 py-3 shadow-2xs">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-blue-50 text-primary p-2 d-flex align-items-center justify-content-center shadow-xs" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-cart-shopping fs-6"></i>
                    </div>
                    <h1 class="h5 fw-bold text-slate-800 mb-0">Pharmacy Orders & Requisitions</h1>
                </div>
                <p class="text-muted small mb-0 mt-1">Centralized management of medication orders, approvals, issuance, and stock receipt.</p>
            </div>

        </div>

        <!-- Metric Badges Row -->
        <div class="row g-2 mt-2">
            <div class="col-6 col-md-auto">
                <a href="{{ route('orders.index', ['status' => 'all']) }}" class="text-decoration-none">
                    <div class="border rounded-xl px-3 py-2 bg-white d-flex align-items-center gap-2 transition hover:bg-slate-50 {{ $statusFilter === 'all' ? 'border-primary ring-1 ring-primary' : '' }}">
                        <span class="text-xs fw-semibold text-slate-500">All Requisitions:</span>
                        <span class="badge bg-slate-200 text-slate-800 rounded-pill">{{ $counts['all'] ?? 0 }}</span>
                    </div>
                </a>
            </div>

            @if(in_array(auth()->user()->role, ['Nurse', 'Head Nurse']) || auth()->user()->hasRole('Nurse') || in_array(strtolower(auth()->user()->role ?? ''), ['nurse', 'head nurse'], true))
            <div class="col-6 col-md-auto">
                <a href="{{ route('orders.index', ['status' => 'my_orders']) }}" class="text-decoration-none">
                    <div class="border rounded-xl px-3 py-2 bg-white d-flex align-items-center gap-2 transition hover:bg-slate-50 {{ $statusFilter === 'my_orders' ? 'border-primary ring-1 ring-primary' : '' }}">
                        <span class="text-xs fw-semibold text-slate-500">My Requests:</span>
                        <span class="badge bg-blue-100 text-primary rounded-pill">{{ $counts['my_orders'] ?? 0 }}</span>
                    </div>
                </a>
            </div>
            @endif

            <div class="col-6 col-md-auto">
                <a href="{{ route('orders.index', ['status' => 'pending']) }}" class="text-decoration-none">
                    <div class="border rounded-xl px-3 py-2 bg-white d-flex align-items-center gap-2 transition hover:bg-slate-50 {{ $statusFilter === 'pending' ? 'border-warning ring-1 ring-warning' : '' }}">
                        <span class="text-xs fw-semibold text-slate-500">Pending Approval:</span>
                        <span class="badge bg-yellow-100 text-yellow-800 rounded-pill">{{ $counts['pending'] ?? 0 }}</span>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-auto">
                <a href="{{ route('orders.index', ['status' => 'pending_issue']) }}" class="text-decoration-none">
                    <div class="border rounded-xl px-3 py-2 bg-white d-flex align-items-center gap-2 transition hover:bg-slate-50 {{ $statusFilter === 'pending_issue' ? 'border-info ring-1 ring-info' : '' }}">
                        <span class="text-xs fw-semibold text-slate-500">Pending Issue:</span>
                        <span class="badge bg-blue-50 text-blue-700 rounded-pill">{{ $counts['pending_issue'] ?? 0 }}</span>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-auto">
                <a href="{{ route('orders.index', ['status' => 'pending_receipt']) }}" class="text-decoration-none">
                    <div class="border rounded-xl px-3 py-2 bg-white d-flex align-items-center gap-2 transition hover:bg-slate-50 {{ $statusFilter === 'pending_receipt' ? 'border-indigo ring-1 ring-indigo' : '' }}">
                        <span class="text-xs fw-semibold text-slate-500">Pending Receipt:</span>
                        <span class="badge bg-indigo-50 text-indigo-700 rounded-pill">{{ $counts['pending_receipt'] ?? 0 }}</span>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-auto">
                <a href="{{ route('orders.index', ['status' => 'received']) }}" class="text-decoration-none">
                    <div class="border rounded-xl px-3 py-2 bg-white d-flex align-items-center gap-2 transition hover:bg-slate-50 {{ $statusFilter === 'received' ? 'border-success ring-1 ring-success' : '' }}">
                        <span class="text-xs fw-semibold text-slate-500">Received / Complete:</span>
                        <span class="badge bg-green-100 text-green-800 rounded-pill">{{ $counts['received'] ?? 0 }}</span>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-auto">
                <a href="{{ route('orders.index', ['status' => 'cancelled']) }}" class="text-decoration-none">
                    <div class="border rounded-xl px-3 py-2 bg-white d-flex align-items-center gap-2 transition hover:bg-slate-50 {{ $statusFilter === 'cancelled' ? 'border-danger ring-1 ring-danger' : '' }}">
                        <span class="text-xs fw-semibold text-slate-500">Cancelled:</span>
                        <span class="badge bg-slate-200 text-slate-600 rounded-pill">{{ $counts['cancelled'] ?? 0 }}</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- Search & Filter Controls Bar -->
        <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-3 mt-3 pt-2 border-top">
            <!-- Filter Pills -->
            <div class="d-flex flex-wrap gap-1">
                @if(in_array(auth()->user()->role, ['Nurse', 'Head Nurse']) || auth()->user()->hasRole('Nurse') || in_array(strtolower(auth()->user()->role ?? ''), ['nurse', 'head nurse'], true))
                <a href="{{ route('orders.index', ['status' => 'my_orders']) }}"
                    class="btn btn-sm {{ $statusFilter === 'my_orders' ? 'btn-primary' : 'btn-light border text-slate-600' }} rounded-pill px-3 py-1 font-semibold text-xs">
                    My Requests ({{ $counts['my_orders'] ?? 0 }})
                </a>
                @endif

                <a href="{{ route('orders.index', ['status' => 'pending']) }}"
                    class="btn btn-sm {{ $statusFilter === 'pending' ? 'btn-primary' : 'btn-light border text-slate-600' }} rounded-pill px-3 py-1 font-semibold text-xs">
                    Pending Approval ({{ $counts['pending'] ?? 0 }})
                </a>

                <a href="{{ route('orders.index', ['status' => 'pending_issue']) }}"
                    class="btn btn-sm {{ $statusFilter === 'pending_issue' ? 'btn-primary' : 'btn-light border text-slate-600' }} rounded-pill px-3 py-1 font-semibold text-xs">
                    Pending Issue ({{ $counts['pending_issue'] ?? 0 }})
                </a>

                <a href="{{ route('orders.index', ['status' => 'all']) }}"
                    class="btn btn-sm {{ $statusFilter === 'all' ? 'btn-primary' : 'btn-light border text-slate-600' }} rounded-pill px-3 py-1 font-semibold text-xs">
                    All Orders ({{ $counts['all'] ?? 0 }})
                </a>

                @if(auth()->check() && (auth()->user()->hasRole('Doctor') || strtolower(auth()->user()->role ?? '') === 'doctor'))
                <form id="approve-all-form" action="{{ route('orders.approveAll') }}" method="POST" class="d-inline-block ms-md-2">
                    @csrf
                    @method('PATCH')
                    <button type="submit" id="btnApproveAll" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-semibold text-xs shadow-xs d-inline-flex align-items-center gap-1.5" data-pending-count="{{ $counts['pending'] ?? 0 }}">
                        <i class="fa-solid fa-check-double"></i>
                        <span>Approve All Pending</span>
                        @if(($counts['pending'] ?? 0) > 0)
                        <span class="badge bg-white text-success rounded-pill px-1.5 ms-0.5">{{ $counts['pending'] }}</span>
                        @endif
                    </button>
                </form>
                @endif
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('orders.index') }}" class="d-flex align-items-center gap-2">
                @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="input-group input-group-sm" style="max-width: 260px;">
                    <span class="input-group-text bg-white border-end-0 text-slate-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search req no, drug, nurse..." class="form-control border-start-0 ps-0 text-xs">
                </div>

                <input type="date" name="date" value="{{ request('date') }}" class="form-control form-control-sm text-xs" style="max-width: 140px;">

                <button type="submit" class="btn btn-sm btn-outline-primary rounded-lg text-xs px-2.5">
                    Filter
                </button>

                @if(request('search') || request('date') || (request('status') && request('status') !== $statusFilter))
                <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-secondary rounded-lg text-xs" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Orders Data Table Container -->
    <div class="flex-grow-1 overflow-auto p-4">
        <div class="card border-0 shadow-sm rounded-2xl overflow-hidden bg-white">
            <div class="table-responsive">
                <table class="table modern-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th scope="col" style="min-width: 130px;">Date & Time</th>
                            <th scope="col" style="min-width: 120px;">Req. No.</th>
                            <th scope="col" style="min-width: 220px;">Medicine Name</th>
                            <th scope="col" style="min-width: 140px;">Qty Requested</th>
                            <th scope="col" style="min-width: 140px;">Requested By</th>
                            <th scope="col" style="min-width: 130px;">MS Approval</th>
                            <th scope="col" style="min-width: 130px;">Qty Issued</th>
                            <th scope="col" style="min-width: 140px;">Issuing Officer</th>
                            <th scope="col" style="min-width: 140px;">Receiving Officer</th>
                            <th scope="col" style="min-width: 130px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orderDetails as $detail)
                        @php
                            $order = $detail->order;
                            $med = $detail->medicine;
                            $unitsPerPack = (int) ($med->units_per_pack ?? 1);
                            $isPackOrdering = $unitsPerPack > 1;

                            $formNameStr = strtolower($med->form->form_name ?? '');
                            if (in_array($formNameStr, ['syrup', 'drops', 'suspension', 'solution', 'iv-fluid'])) {
                                $itemUnit = 'Bottle(s)';
                            } elseif (in_array($formNameStr, ['injection'])) {
                                $itemUnit = 'Vial(s)';
                            } elseif (in_array($formNameStr, ['capsule', 'tablet', 'pill'])) {
                                $itemUnit = ucfirst($formNameStr) . '(s)';
                            } else {
                                $itemUnit = !empty($med->unit->unit_name) ? ucfirst($med->unit->unit_name) . '(s)' : 'Unit(s)';
                            }

                            $actualRequested = (int) $detail->qty_requested;
                            $reqPacks = $isPackOrdering && $unitsPerPack > 0 ? round($actualRequested / $unitsPerPack, 2) : 0;
                            $displayReqPacks = (float)$reqPacks == (int)$reqPacks ? (int)$reqPacks : $reqPacks;

                            $actualIssued = (int) $detail->qty_issued;
                            $issuedPacks = $isPackOrdering && $unitsPerPack > 0 ? round($actualIssued / $unitsPerPack, 2) : 0;
                            $displayIssuedPacks = (float)$issuedPacks == (int)$issuedPacks ? (int)$issuedPacks : $issuedPacks;

                            $isDoctor = auth()->check() && (auth()->user()->hasRole('Doctor') || strtolower(auth()->user()->role ?? '') === 'doctor');
                            $isPharmacist = auth()->check() && (auth()->user()->hasRole('Pharmacist') || strtolower(auth()->user()->role ?? '') === 'pharmacist');
                            $isNurse = auth()->check() && (in_array(auth()->user()->role, ['Nurse', 'Head Nurse']) || auth()->user()->hasRole('Nurse') || in_array(strtolower(auth()->user()->role ?? ''), ['nurse', 'head nurse'], true));

                            $isCreatorNurse = $isNurse && (int)($order->requested_by ?? 0) === (int)auth()->id();
                            $isPending = strtolower($order->ms_approval_status ?? '') === 'pending';
                            $isApproved = strtolower($order->ms_approval_status ?? '') === 'approved';
                            $isCancelled = strtolower($order->ms_approval_status ?? '') === 'cancelled';
                            $isRejected = strtolower($order->ms_approval_status ?? '') === 'rejected';

                            $isReceived = str_contains($detail->remark ?? '', '[RECEIVED_BY:') || str_contains($detail->remark ?? '', '[RECEIVED]');
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <!-- Date & Time -->
                            <td>
                                <div class="fw-bold text-slate-800" style="font-size: 0.825rem;">
                                    {{ $order->date ? \Carbon\Carbon::parse($order->date)->format('M d, Y') : 'N/A' }}
                                </div>
                                <small class="text-muted" style="font-size: 0.725rem;">
                                    <i class="fa-regular fa-clock me-1"></i>{{ $order->date ? \Carbon\Carbon::parse($order->date)->format('h:i A') : 'N/A' }}
                                </small>
                            </td>

                            <!-- Req. No. -->
                            <td>
                                <span class="badge bg-blue-50 text-primary border border-blue-100 fw-bold px-2.5 py-1.5 rounded-lg" style="font-size: 0.775rem;">
                                    {{ $order->req_no ?? 'N/A' }}
                                </span>
                            </td>

                            <!-- Medicine Name (NEW COLUMN) -->
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-slate-900" style="font-size: 0.85rem;">
                                        {{ $med->name ?? 'Unknown Medicine' }}
                                    </span>
                                    <div class="d-flex align-items-center gap-1.5 mt-1 flex-wrap">
                                        @if($med && $med->category)
                                        <span class="badge bg-slate-100 text-slate-700 border rounded-md" style="font-size: 0.675rem;">
                                            {{ $med->category->name }}
                                        </span>
                                        @endif
                                        @if($med && $med->strength)
                                        <span class="text-muted" style="font-size: 0.725rem;">
                                            {{ $med->strength }}
                                        </span>
                                        @endif
                                        @if($med && $med->form)
                                        <span class="text-muted" style="font-size: 0.725rem;">
                                            • {{ $med->form->form_name }}
                                        </span>
                                        @endif
                                        @if($isPackOrdering)
                                        <span class="badge bg-purple-50 text-purple-700 border border-purple-100 rounded-md" style="font-size: 0.675rem;">
                                            {{ $unitsPerPack }}/pack
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Qty Requested -->
                            <td>
                                @if($isDoctor && $isPending)
                                <form id="doctor-approve-form-{{ $detail->id }}" action="{{ route('orders.approve', $detail->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                </form>
                                <div class="d-flex align-items-center gap-1">
                                    <input type="number" form="doctor-approve-form-{{ $detail->id }}" name="qty_requested" value="{{ $actualRequested }}" min="1" class="form-control form-control-sm text-center fw-bold text-xs" style="width: 70px;" required>
                                    <span class="text-muted text-xs">{{ $itemUnit }}</span>
                                </div>
                                @else
                                <div class="fw-bold text-slate-800">
                                    @if($isPackOrdering && $unitsPerPack > 0)
                                    {{ $displayReqPacks }} Pack(s)
                                    <div class="text-muted fw-normal" style="font-size: 0.725rem;">(Total: {{ $actualRequested }} {{ $itemUnit }})</div>
                                    @else
                                    {{ $actualRequested }} <span class="text-muted fw-normal" style="font-size: 0.75rem;">{{ $itemUnit }}</span>
                                    @endif
                                </div>
                                @endif
                            </td>

                            <!-- Requested By -->
                            <td>
                                <div class="fw-semibold text-slate-800" style="font-size: 0.8rem;">
                                    {{ $order->requester->name ?? 'N/A' }}
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem;">
                                    {{ $order->ward->ward_name ?? 'Ward' }}
                                </small>
                            </td>

                            <!-- MS Approval -->
                            <td>
                                <div>
                                    @if($isApproved)
                                    <span class="badge bg-green-100 text-green-800 border border-green-200 rounded-pill px-2.5 py-1 text-xs fw-semibold">
                                        <i class="fa-solid fa-check me-1"></i>Approved
                                    </span>
                                    @elseif($isRejected)
                                    <span class="badge bg-red-100 text-red-700 border border-red-200 rounded-pill px-2.5 py-1 text-xs fw-semibold">
                                        <i class="fa-solid fa-xmark me-1"></i>Rejected
                                    </span>
                                    @elseif($isCancelled)
                                    <span class="badge bg-slate-200 text-slate-600 rounded-pill px-2.5 py-1 text-xs fw-semibold">
                                        Cancelled
                                    </span>
                                    @else
                                    <span class="badge bg-yellow-100 text-yellow-800 border border-yellow-200 rounded-pill px-2.5 py-1 text-xs fw-semibold">
                                        <i class="fa-regular fa-clock me-1"></i>Pending
                                    </span>
                                    @endif

                                    @if($order->approver)
                                    <div class="text-muted mt-1" style="font-size: 0.7rem;">
                                        By: {{ $order->approver->name }}
                                    </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Qty Issued -->
                            <td>
                                @if($isPharmacist && $isApproved && $detail->qty_issued == 0)
                                <form id="pharmacist-issue-form-{{ $detail->id }}" action="{{ route('orders.issue', $detail->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                </form>
                                <div class="d-flex align-items-center gap-1">
                                    <input type="number" form="pharmacist-issue-form-{{ $detail->id }}" name="qty_issued" value="{{ $actualRequested }}" max="{{ $actualRequested }}" min="1" class="form-control form-control-sm text-center fw-bold text-xs" style="width: 70px;" required>
                                    <span class="text-muted text-xs">{{ $itemUnit }}</span>
                                </div>
                                @else
                                <div class="fw-bold text-slate-800">
                                    @if($detail->qty_issued > 0)
                                        @if($isPackOrdering && $unitsPerPack > 0)
                                        {{ $displayIssuedPacks }} Pack(s)
                                        <div class="text-muted fw-normal" style="font-size: 0.725rem;">(Total: {{ $actualIssued }} {{ $itemUnit }})</div>
                                        @else
                                        {{ $actualIssued }} <span class="text-muted fw-normal" style="font-size: 0.75rem;">{{ $itemUnit }}</span>
                                        @endif
                                    @else
                                    <span class="text-muted">0</span>
                                    @endif
                                </div>
                                @endif
                            </td>

                            <!-- Issuing Officer -->
                            <td>
                                @if(str_contains($detail->remark ?? '', '[ISSUED_BY:'))
                                    @php
                                    preg_match('/\[ISSUED_BY:(.*?)\]/', $detail->remark ?? '', $issMatches);
                                    $issName = $issMatches[1] ?? 'Pharmacist';
                                    @endphp
                                    <div class="fw-semibold text-slate-700" style="font-size: 0.8rem;">{{ $issName }}</div>
                                    <span class="badge bg-blue-50 text-blue-700 rounded-pill px-2 py-0.5 text-2xs">Issued</span>
                                @elseif($isPharmacist && $isApproved && $detail->qty_issued == 0)
                                    <button type="submit" form="pharmacist-issue-form-{{ $detail->id }}" class="btn btn-sm btn-primary rounded-lg text-xs px-2.5 py-1 fw-semibold shadow-xs">
                                        <i class="fa-solid fa-box-open me-1"></i>Issue Batch
                                    </button>
                                @elseif($isCancelled)
                                    <span class="text-muted text-xs">Cancelled</span>
                                @else
                                    <span class="text-muted text-xs">Waiting for Issue</span>
                                @endif
                            </td>

                            <!-- Receiving Officer -->
                            <td>
                                @if($isReceived)
                                    @php
                                    preg_match('/\[RECEIVED_BY:(.*?)\]/', $detail->remark ?? '', $rxMatches);
                                    $rxName = $rxMatches[1] ?? '';
                                    @endphp
                                    <span class="badge bg-indigo-100 text-indigo-800 border border-indigo-200 rounded-pill px-2.5 py-1 text-xs fw-semibold">
                                        <i class="fa-solid fa-check-double me-1"></i>Received
                                    </span>
                                    @if($rxName)
                                    <div class="text-muted mt-1" style="font-size: 0.7rem;">
                                        By: {{ $rxName }}
                                    </div>
                                    @endif
                                @elseif($isCancelled)
                                    <span class="text-muted text-xs">Cancelled</span>
                                @elseif($detail->qty_issued == 0)
                                    <span class="text-muted text-xs">Waiting for Issue</span>
                                @elseif($isNurse)
                                    <button type="button" 
                                            class="btn btn-sm btn-indigo text-white rounded-lg text-xs px-2.5 py-1 fw-semibold shadow-xs"
                                            style="background: #4f46e5;"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#confirmReceiptModal" 
                                            data-id="{{ $detail->id }}" 
                                            data-med="{{ $med->name ?? 'Medicine' }}"
                                            data-qty="{{ $detail->qty_issued }}"
                                            data-unit="{{ $itemUnit }}"
                                            data-batches='@json($med->batches ?? [])'>
                                        <i class="fa-solid fa-box-archive me-1"></i>Confirm Receipt
                                    </button>
                                @else
                                    <span class="badge bg-slate-100 text-slate-500 rounded-pill text-xs">Pending Receipt</span>
                                @endif
                            </td>

                            <!-- Action Column -->
                            <td class="text-center">
                                @if($isDoctor && $isPending)
                                    <!-- Doctor: Approve & Reject buttons -->
                                    <div class="d-flex items-center justify-content-center gap-1.5">
                                        <button type="submit" form="doctor-approve-form-{{ $detail->id }}" name="action" value="Approve" class="btn btn-sm btn-success rounded-lg text-xs px-2.5 py-1 fw-semibold shadow-xs">
                                            <i class="fa-solid fa-check me-1"></i>Approve
                                        </button>
                                        <button type="submit" form="doctor-approve-form-{{ $detail->id }}" name="action" value="Reject" onclick="return confirm('Are you sure you want to reject this requisition?');" class="btn btn-sm btn-danger rounded-lg text-xs px-2.5 py-1 fw-semibold shadow-xs">
                                            <i class="fa-solid fa-xmark me-1"></i>Reject
                                        </button>
                                    </div>
                                @elseif($isCreatorNurse && $isPending)
                                    <!-- Nurse Creator: Cancel button with SweetAlert2 -->
                                    <form action="{{ route('orders.cancel', $detail->id) }}" method="POST" class="cancel-order-form d-inline-block" data-req-no="{{ $order->req_no ?? 'this requisition' }}" data-med-name="{{ $med->name ?? '' }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-lg text-xs px-2.5 py-1 fw-semibold shadow-2xs">
                                            <i class="fa-solid fa-ban me-1"></i>Cancel
                                        </button>
                                    </form>
                                @elseif($isPharmacist && $isApproved && $detail->qty_issued == 0)
                                    <!-- Pharmacist quick action -->
                                    <button type="submit" form="pharmacist-issue-form-{{ $detail->id }}" class="btn btn-sm btn-primary rounded-lg text-xs px-2.5 py-1 fw-semibold shadow-xs">
                                        Issue
                                    </button>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted d-flex flex-column align-items-center">
                                    <div class="rounded-circle bg-slate-100 p-3 mb-2">
                                        <i class="fa-solid fa-box-open fs-2 text-slate-400"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1 text-slate-700">No Pharmacy Requisitions Found</h6>
                                    <p class="text-muted small mb-0">There are no orders matching your current role or filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Confirm Receipt Modal (Nurse action) -->
<div class="modal fade" id="confirmReceiptModal" tabindex="-1" aria-labelledby="confirmReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-2xl">
            <form id="receiptForm" method="POST" action="">
                @csrf
                <div class="modal-header border-bottom px-4 py-3 bg-slate-50">
                    <div>
                        <h5 class="modal-title fw-bold text-slate-800" id="confirmReceiptModalLabel">
                            <i class="fa-solid fa-box-archive text-primary me-2"></i>Confirm Stock Receipt
                        </h5>
                        <p class="text-muted small mb-0">Verify and receive the issued medication batch into ward inventory.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- Medicine and Quantity Summary Banner -->
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block fw-semibold" style="font-size: 0.725rem;">MEDICINE</small>
                                <span class="fw-bold text-primary" id="modalMedicineName">Medicine Name</span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block fw-semibold" style="font-size: 0.725rem;">QTY ISSUED</small>
                                <span class="fw-bold text-slate-800" id="modalQtyIssued">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Batch Option Radios -->
                    <label class="form-label mb-2">Batch Selection <span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center gap-4 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="batch_type" id="batchTypeExisting" value="existing" checked onchange="toggleBatchMode(false)">
                            <label class="form-check-label text-sm fw-semibold text-slate-700" for="batchTypeExisting">
                                Existing Batch
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="batch_type" id="batchTypeNew" value="new" onchange="toggleBatchMode(true)">
                            <label class="form-check-label text-sm fw-semibold text-slate-700" for="batchTypeNew">
                                New Batch
                            </label>
                        </div>
                    </div>

                    <!-- Existing Batch Container -->
                    <div id="existingBatchContainer" class="mb-3">
                        <label for="batchSelect" class="form-label">Select Received Batch <span class="text-danger">*</span></label>
                        <select name="batch_id" id="batchSelect" class="form-select text-sm" required>
                            <option value="" disabled selected>Select an existing batch...</option>
                        </select>
                        <small class="text-muted" style="font-size: 0.725rem;">Select from currently registered batches of this medicine.</small>
                    </div>

                    <!-- New Batch Container (Hidden by default) -->
                    <div id="newBatchContainer" class="row g-3 mb-3 d-none">
                        <div class="col-sm-6">
                            <label for="newBatchNo" class="form-label">New Batch Number <span class="text-danger">*</span></label>
                            <input type="text" name="new_batch_no" id="newBatchNo" placeholder="e.g. BATCH-2026-X" class="form-control text-sm uppercase" oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="col-sm-6">
                            <label for="newExpiryDate" class="form-label">Expiry Date <span class="text-danger">*</span></label>
                            <input type="date" name="new_expiry_date" id="newExpiryDate" class="form-control text-sm">
                        </div>
                    </div>

                    <div class="alert alert-light border rounded-xl py-2 px-3 text-xs text-muted mb-0">
                        <i class="fa-solid fa-circle-info text-primary me-1"></i>
                        Confirming receipt will automatically log a Stock Receipt and increment the ward stock ledger.
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-3 bg-slate-50 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-xl text-xs px-3 py-2 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-xl text-xs px-4 py-2 fw-semibold shadow-xs">
                        <i class="fa-solid fa-check me-1"></i>Confirm Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleBatchMode(isNew) {
    const existingContainer = document.getElementById('existingBatchContainer');
    const newContainer = document.getElementById('newBatchContainer');
    const batchSelect = document.getElementById('batchSelect');
    const newBatchNo = document.getElementById('newBatchNo');
    const newExpiryDate = document.getElementById('newExpiryDate');

    if (isNew) {
        existingContainer.classList.add('d-none');
        newContainer.classList.remove('d-none');
        batchSelect.removeAttribute('required');
        newBatchNo.setAttribute('required', 'required');
        newExpiryDate.setAttribute('required', 'required');
    } else {
        existingContainer.classList.remove('d-none');
        newContainer.classList.add('d-none');
        batchSelect.setAttribute('required', 'required');
        newBatchNo.removeAttribute('required');
        newExpiryDate.removeAttribute('required');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const receiptModal = document.getElementById('confirmReceiptModal');
    if (receiptModal) {
        receiptModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const detailId = button.getAttribute('data-id');
            const medName = button.getAttribute('data-med');
            const qtyIssued = button.getAttribute('data-qty');
            const unit = button.getAttribute('data-unit');
            const batches = JSON.parse(button.getAttribute('data-batches') || '[]');

            // Set form action
            document.getElementById('receiptForm').action = "{{ url('orders') }}/" + detailId + "/receive";
            document.getElementById('modalMedicineName').textContent = medName;
            document.getElementById('modalQtyIssued').textContent = qtyIssued + ' ' + unit;

            // Reset radio to existing
            document.getElementById('batchTypeExisting').checked = true;
            toggleBatchMode(false);

            // Populate batches dropdown
            const batchSelect = document.getElementById('batchSelect');
            batchSelect.innerHTML = '<option value="" disabled selected>Select an existing batch...</option>';

            if (batches.length > 0) {
                batches.forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = b.id;
                    opt.textContent = `${b.batch_no} (Expires: ${b.expiry_date || 'N/A'})`;
                    batchSelect.appendChild(opt);
                });
            } else {
                // If medicine has no existing batches, auto-switch to new batch
                document.getElementById('batchTypeNew').checked = true;
                toggleBatchMode(true);
            }
        });
    }

    // SweetAlert2 confirmation for order cancellation
    document.querySelectorAll('.cancel-order-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const reqNo = form.getAttribute('data-req-no') || 'this requisition';
            const medName = form.getAttribute('data-med-name') || '';
            const drugDetails = medName ? `<div class="fw-semibold text-slate-800 mt-1 mb-2">${medName}</div>` : '';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Are you sure?',
                    html: `Are you sure you want to cancel order requisition <span class="badge bg-blue-50 text-primary border border-blue-200 fw-bold px-2 py-1 rounded">${reqNo}</span>?${drugDetails}<div class="text-muted small mt-2">This requisition will be marked as <strong>Cancelled</strong> and cannot be undone.</div>`,
                    icon: 'warning',
                    iconColor: '#f59e0b',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa-solid fa-ban me-1"></i> Yes, cancel order',
                    cancelButtonText: 'No, keep it',
                    reverseButtons: true,
                    focusCancel: true,
                    buttonsStyling: false,
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border-0 p-4',
                        title: 'fw-bold text-slate-800 fs-5 mt-2',
                        htmlContainer: 'text-slate-600 text-sm my-2',
                        confirmButton: 'btn btn-danger rounded-xl px-3.5 py-2 text-xs fw-semibold shadow-sm',
                        cancelButton: 'btn btn-outline-secondary rounded-xl px-3.5 py-2 text-xs fw-semibold',
                        actions: 'd-flex gap-2 justify-content-center mt-3'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                form.submit();
            }
        });
    });

    // SweetAlert2 confirmation for bulk approve all orders (Doctor action)
    const approveAllForm = document.getElementById('approve-all-form');
    if (approveAllForm) {
        approveAllForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = document.getElementById('btnApproveAll');
            const pendingCount = btn ? (btn.getAttribute('data-pending-count') || '0') : '0';
            const countHtml = parseInt(pendingCount) > 0 
                ? `<div class="mt-2"><span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200 fw-bold px-2.5 py-1 rounded-pill">${pendingCount} order requisition(s)</span> will be approved.</div>`
                : '<div class="mt-2 text-muted small">All requisitions currently awaiting approval will be marked as Approved.</div>';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Are you sure?',
                    html: `Are you sure you want to approve all pending orders?${countHtml}`,
                    icon: 'question',
                    iconColor: '#10b981',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa-solid fa-check-double me-1"></i> Yes, approve all',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    focusCancel: true,
                    buttonsStyling: false,
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border-0 p-4',
                        title: 'fw-bold text-slate-800 fs-5 mt-2',
                        htmlContainer: 'text-slate-600 text-sm my-2',
                        confirmButton: 'btn btn-success rounded-xl px-3.5 py-2 text-xs fw-semibold shadow-sm',
                        cancelButton: 'btn btn-outline-secondary rounded-xl px-3.5 py-2 text-xs fw-semibold',
                        actions: 'd-flex gap-2 justify-content-center mt-3'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        approveAllForm.submit();
                    }
                });
            } else {
                approveAllForm.submit();
            }
        });
    }

    // Auto-dismiss success alert messages after 3 seconds
    const successAlerts = document.querySelectorAll('.alert-success');
    successAlerts.forEach(function (alert) {
        if (!alert.dataset.dismissScheduled) {
            alert.dataset.dismissScheduled = 'true';
            setTimeout(function () {
                alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function () {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 500);
            }, 3000);
        }
    });
});
</script>
@endsection
