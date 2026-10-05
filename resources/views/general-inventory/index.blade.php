@extends('layouts.app')

@section('title', 'General Inventory - Ward 48')

@section('content')

    <!-- Left Sidebar: General Items List -->
    <x-inventory-sidebar 
        brandTitle="Ward Inventory"
        brandSubtitle="GENERAL WARD SUPPLIES"
        searchPlaceholder=" Search items by name or code..."
        :searchValue="request('search', '')"
        addButtonLabel="Add Item"
        addModalId="createGeneralItemModal"
        :showAddButton="auth()->user()->hasRole('Staff Nurse')"
    >
    >
        @forelse($items as $item)
            @php
                $isActive = $selectedItem && $selectedItem->id === $item->id;
            @endphp
            <div class="sidebar-item-card {{ $isActive ? 'active' : '' }}">
                <div class="d-flex align-items-center justify-content-between">
                    <a href="{{ route('general-inventory.index', array_merge(request()->query(), ['selected_item' => $item->id])) }}" 
                       class="text-decoration-none flex-grow-1">
                        <div class="sidebar-item-name">{{ $item->name }}</div>
                        <div class="d-flex align-items-center gap-2">
                            <small class="text-muted fw-semibold" style="font-size: 0.725rem;">{{ $item->item_code }}</small>
                            <small class="text-muted" style="font-size: 0.725rem;">• {{ $item->category?->name ?? 'General' }}</small>
                        </div>
                    </a>

                    <div class="text-end">
                        <span class="sidebar-item-badge">
                            {{ $item->computed_balance }} Units
                        </span>
                        @if($item->is_low_stock)
                            <div class="mt-1">
                                <span class="low-stock-alert-pill">
                                    <i class="bi bi-exclamation-circle-fill"></i> LOW
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mt-2 pt-1 border-top border-light-subtle">
                    <div class="sidebar-item-actions">
                        @if(auth()->user()->hasRole('Staff Nurse'))
                            <button type="button" 
                                    class="action-icon-btn" 
                                    title="Edit Item" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editItemModal{{ $item->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                        @endif
                        <a href="{{ route('general-inventory.index', ['selected_item' => $item->id]) }}" 
                           class="action-icon-btn" 
                           title="View Details">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem;">
                        {{ $item->transactions_count ?? $item->transactions()->count() }} entries
                    </small>
                </div>
            </div>

                        <!-- Edit Item Modal (nurse-only) -->
            @if(auth()->user()->hasRole('Staff Nurse'))
            <div class="modal fade" id="editItemModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('general-inventory.update', $item->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">Edit General Item</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Item Code</label>
                                    <input type="text" name="item_code" class="form-control" value="{{ old('item_code', $item->item_code) }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Item Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $item->name) }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select name="category_id" class="form-select" required>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ (old('category_id', $item->category_id) == $cat->id) ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary rounded-pill px-4">Update Item</button>
                            </div>
                                               </form>
                    </div>
                </div>
            </div>
            @endif
        @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                <div class="fw-semibold">No general items found</div>
                <small>Click "Add Item" to create one.</small>
            </div>
        @endforelse
    </x-inventory-sidebar>

    <!-- Main Content Panel -->
    <main class="main-content-panel">
        @if($selectedItem)
            <!-- Top Gradient Header Banner -->
            <x-header-card 
                :title="$selectedItem->name"
                subtitle="Item Code: {{ $selectedItem->item_code }} • Category: {{ $selectedItem->category?->name ?? 'General' }} • Neuro Surgical Ward 48"
                :metricValue="$selectedItem->computed_balance . ' Units'"
                metricLabel="AVAILABLE"
                :metricColor="$selectedItem->is_low_stock ? '#dc2626' : '#16a34a'"
            />

            <!-- Subnav Tabs & Filter Bar -->
            <div class="subnav-filter-bar">
                <!-- Nav Tabs -->
                <div class="custom-nav-tabs">
                    <button class="custom-nav-tab-btn {{ request('tab', 'transactions') === 'transactions' ? 'active' : '' }}"
                            onclick="switchTab('transactionsTab')">
                        <i class="bi bi-arrow-left-right me-1"></i>
                        Transactions Log ({{ $transactions->count() }})
                    </button>
                    <button class="custom-nav-tab-btn {{ request('tab') === 'adjustments' ? 'active' : '' }}"
                            onclick="switchTab('adjustmentsTab')">
                        <i class="bi bi-sliders me-1"></i>
                        Stock Adjustments ({{ $adjustments->count() }})
                    </button>
                </div>

                <!-- Filters -->
                <form method="GET" action="{{ route('general-inventory.index') }}" class="filter-controls-group">
                    <input type="hidden" name="selected_item" value="{{ $selectedItem->id }}">
                    <input type="hidden" name="tab" id="activeTabInput" value="{{ request('tab', 'transactions') }}">
                    
                    <div class="d-flex align-items-center">
                        <i class="bi bi-search text-muted me-1" style="font-size: 0.8rem;"></i>
                        <input type="text" 
                               name="filter_search" 
                               value="{{ request('filter_search') }}" 
                               class="filter-search-input" 
                               placeholder="Search records...">
                    </div>

                    <input type="date" 
                           name="filter_date" 
                           value="{{ request('filter_date') }}" 
                           class="filter-date-input"
                           title="Filter by Date">

                    <button type="submit" class="btn btn-sm btn-primary rounded-3 px-2 py-1" style="font-size: 0.78rem;">
                        Filter
                    </button>

                    @if(request('filter_search') || request('filter_date'))
                        <a href="{{ route('general-inventory.index', ['selected_item' => $selectedItem->id, 'tab' => request('tab', 'transactions')]) }}" 
                           class="filter-clear-btn">
                            ✕ Clear
                        </a>
                    @endif
                </form>
            </div>

            <!-- Tab 1: General Transactions Log -->
            <div id="transactionsTab" style="{{ request('tab', 'transactions') === 'transactions' ? '' : 'display: none;' }}">
                                <x-log-table 
                    title="General Transactions Log"
                    :actionModalId="auth()->user()->hasRole('Staff Nurse') ? 'addTransactionModal' : null"
                    actionLabel="Add Transaction"
                >
                    <x-slot:headers>
                        <th>Date & Time</th>
                        <th>Ward</th>
                        <th>Qty Received</th>
                        <th>Qty Issued</th>
                        <th>Running Balance</th>
                        <th>Recorded By</th>
                    </x-slot:headers>

                    @forelse($transactions as $tx)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $tx->date?->format('M d, Y') ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $tx->date?->format('h:i A') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $tx->ward?->ward_number ?? 'Ward 48' }}
                                </span>
                            </td>
                            <td>
                                @if($tx->quantity_received > 0)
                                    <span class="badge-received">+ {{ $tx->quantity_received }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($tx->quantity_issued > 0)
                                    <span class="badge-issued">- {{ $tx->quantity_issued }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-running-balance">
                                    {{ $tx->running_balance }} Units
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-person-circle text-secondary"></i>
                                    <span>{{ $tx->recorder?->name ?? 'System Officer' }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary"></i>
                                <div>No transactions recorded yet for <strong>{{ $selectedItem->name }}</strong>.</div>
                                <small>Click "Add Transaction" above to log a receipt or issue.</small>
                            </td>
                        </tr>
                    @endforelse
                </x-log-table>
            </div>

            <!-- Tab 2: Stock Adjustments Log -->
            <div id="adjustmentsTab" style="{{ request('tab') === 'adjustments' ? '' : 'display: none;' }}">
                <x-log-table 
                    title="Stock Adjustments & Loss/Damage Log"
                    :actionModalId="auth()->user()->hasRole('Staff Nurse') ? 'addAdjustmentModal' : null"
                    actionLabel="Add Adjustment"
                    
                >
                    <x-slot:headers>
                        <th>Date</th>
                        <th>Adjustment Type</th>
                        <th>Quantity</th>
                        <th>Reason / Notes</th>
                        <th>Ward</th>
                        <th>Adjusted By</th>
                    </x-slot:headers>

                    @forelse($adjustments as $adj)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $adj->created_at?->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $adj->created_at?->format('h:i A') }}</small>
                            </td>
                            <td>
                                @php
                                    $typeClasses = [
                                        'DAMAGED' => 'bg-danger-subtle text-danger border-danger-subtle',
                                        'LOST' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                        'COUNT_CORRECTION' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                                        'RETURN' => 'bg-success-subtle text-success border-success-subtle',
                                    ];
                                @endphp
                                <span class="badge border {{ $typeClasses[$adj->adjustment_type] ?? 'bg-light text-dark' }}">
                                    {{ str_replace('_', ' ', $adj->adjustment_type) }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold {{ $adj->quantity < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $adj->quantity > 0 ? '+' : '' }}{{ $adj->quantity }} Units
                                </span>
                            </td>
                            <td style="max-width: 250px;">
                                <div class="text-truncate" title="{{ $adj->reason }}">{{ $adj->reason }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $adj->ward?->ward_number ?? 'Ward 48' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-shield-check text-primary"></i>
                                    <span>{{ $adj->adjuster?->name ?? 'Authorizer' }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-check fs-1 d-block mb-2 text-secondary"></i>
                                <div>No stock adjustments logged for <strong>{{ $selectedItem->name }}</strong>.</div>
                                <small>Click "Add Adjustment" to record damages, losses, or count audits.</small>
                            </td>
                        </tr>
                    @endforelse
                </x-log-table>
            </div>

            <!-- Modal: Add Transaction (Receipt or Issue) (nurse-only) -->
            @if(auth()->user()->hasRole('Staff Nurse'))
            <div class="modal fade" id="addTransactionModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('general-inventory.transactions.store', $selectedItem->id) }}">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $selectedItem->id }}">

                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title fw-bold">Log General Item Transaction</h5>
                                    <small class="text-muted">{{ $selectedItem->name }} ({{ $selectedItem->item_code }})</small>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Transaction Type</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="transaction_type" id="typeReceipt" value="RECEIPT" checked>
                                            <label class="form-check-label fw-semibold text-success" for="typeReceipt">
                                                <i class="bi bi-box-arrow-in-down me-1"></i> Quantity Received (Stock In)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="transaction_type" id="typeIssue" value="ISSUE">
                                            <label class="form-check-label fw-semibold text-primary" for="typeIssue">
                                                <i class="bi bi-box-arrow-up me-1"></i> Quantity Issued (Stock Out)
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" name="quantity" min="1" class="form-control" placeholder="e.g. 20" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Ward</label>
                                        <select name="ward_id" class="form-select" required>
                                            @foreach($wards as $w)
                                                <option value="{{ $w->id }}">{{ $w->ward_number }} </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Transaction Date & Time</label>
                                        <input type="datetime-local" name="date" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Recorded By (Officer)</label>
                                        <select name="recorded_by" class="form-select" required>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary rounded-pill px-4">Record Transaction</button>
                            </div>
                        </form>
                                      </div>
                </div>
            </div>
            @endif

            <!-- Modal: Add Stock Adjustment (nurse-only) -->
            @if(auth()->user()->hasRole('Staff Nurse'))
            <div class="modal fade" id="addAdjustmentModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('general-inventory.adjustments.store', $selectedItem->id) }}">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $selectedItem->id }}">

                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title fw-bold">Record Stock Adjustment</h5>
                                    <small class="text-muted">{{ $selectedItem->name }} ({{ $selectedItem->item_code }})</small>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Adjustment Type</label>
                                        <select name="adjustment_type" class="form-select" required>
                                            <option value="DAMAGED">DAMAGED (Damaged / Torn)</option>
                                            <option value="LOST">LOST (Missing from Ward)</option>
                                            <option value="COUNT_CORRECTION">COUNT CORRECTION (Audit reconciliation)</option>
                                            <option value="RETURN">RETURN (Returned to supplier/central store)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" name="quantity" class="form-control" placeholder="e.g. 5 or -5" required>
                                        <small class="text-muted" style="font-size: 0.72rem;">Damaged & lost auto-reduce stock.</small>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Ward</label>
                                        <select name="ward_id" class="form-select" required>
                                            @foreach($wards as $w)
                                                <option value="{{ $w->id }}">{{ $w->ward_number }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Authorizing Officer</label>
                                        <select name="adjusted_by" class="form-select" required>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Audit Reason / Discrepancy Explanation</label>
                                    <textarea name="reason" class="form-control" rows="3" placeholder="State reason for loss, laundry contamination, or physical count discrepancy..." required></textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary rounded-pill px-4">Save Adjustment</button>
                            </div>
                                               </form>
                    </div>
                </div>
            </div>
            @endif

        @else
            <div class="text-center py-5 my-5">
                <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-box-seam fs-1"></i>
                </div>
                <h3>No Items in General Inventory</h3>
                <p class="text-muted">Create your first ward general inventory item to view running balances and transaction logs.</p>
                @if(auth()->user()->hasRole('Staff Nurse'))
                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2" data-bs-toggle="modal" data-bs-target="#createGeneralItemModal">
                        <i class="bi bi-plus-lg me-1"></i> Add General Item
                    </button>
                @endif
            </div>
        @endif
    </main>

        <!-- Modal: Create New General Item (nurse-only) -->
    @if(auth()->user()->hasRole('Staff Nurse'))
    <div class="modal fade" id="createGeneralItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('general-inventory.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Add New General Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Item Code</label>
                            <input type="text" name="item_code" class="form-control" placeholder="e.g. GEN-LIN-005" required>
                            <small class="text-muted" style="font-size: 0.72rem;">Unique stock tracking identifier</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Item Description / Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Cotton Drawsheets (White)" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Create Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

@endsection

@push('scripts')
<script>
    function switchTab(tabId) {
        document.getElementById('transactionsTab').style.display = tabId === 'transactionsTab' ? '' : 'none';
        document.getElementById('adjustmentsTab').style.display = tabId === 'adjustmentsTab' ? '' : 'none';
        
        document.querySelectorAll('.custom-nav-tab-btn').forEach(btn => btn.classList.remove('active'));
        if (event && event.currentTarget) {
            event.currentTarget.classList.add('active');
        }
        
        const activeTabInput = document.getElementById('activeTabInput');
        if (activeTabInput) {
            activeTabInput.value = tabId === 'transactionsTab' ? 'transactions' : 'adjustments';
        }
    }
</script>
@endpush
