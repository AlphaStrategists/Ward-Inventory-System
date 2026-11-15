@props([
    'brandTitle' => 'Ward Inventory',
    'brandSubtitle' => 'WARD 48 INVENTORY',
    'searchPlaceholder' => 'Search items...',
    'searchValue' => '',
        'addButtonLabel' => 'Add Item',
    'addModalId' => 'createItemModal',
    'showAddButton' => true,
])

<aside class="inventory-sidebar">
    <div class="sidebar-brand-header">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-box-seam-fill text-primary fs-5"></i>
            <h5 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.01em;">{{ $brandTitle }}</h5>
        </div>
        <div class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 0.08em;">
            {{ $brandSubtitle }}
        </div>
    </div>

    <!-- Search Box -->
    <div class="sidebar-search-box">
        <form method="GET" action="{{ url()->current() }}">
            <i class="bi bi-search"></i>
            <input type="text" 
                   name="search" 
                   value="{{ $searchValue }}" 
                   class="form-control sidebar-search-input" 
                   placeholder="{{ $searchPlaceholder }}"
                   onkeydown="if(event.key === 'Enter'){ this.form.submit(); }">
            @if(request('selected_item'))
                <input type="hidden" name="selected_item" value="{{ request('selected_item') }}">
            @endif
            @if(request('selected_patient'))
                <input type="hidden" name="selected_patient" value="{{ request('selected_patient') }}">
            @endif
        </form>
    </div>

        <!-- Add New Item / Patient Action Button (nurse-only) -->
    @if($showAddButton)
        <button type="button" class="sidebar-action-btn" data-bs-toggle="modal" data-bs-target="#{{ $addModalId }}">
            <i class="bi bi-plus-lg"></i>
            <span>{{ $addButtonLabel }}</span>
        </button>
    @endif

    <!-- Scrollable Items List -->
    <div class="sidebar-list-container">
        {{ $slot }}
    </div>
</aside>
