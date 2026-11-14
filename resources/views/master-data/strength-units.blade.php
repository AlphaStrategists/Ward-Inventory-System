@extends('layouts.app')

@section('title', 'Strength Units - Master Data')

@section('content')
<div class="d-flex flex-column flex-grow-1 overflow-hidden bg-slate-50 w-100">

    <!-- Top Action & Filter Header -->
    <div class="bg-white border-bottom px-4 py-3 shadow-2xs">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-blue-50 text-primary p-2 d-flex align-items-center justify-content-center shadow-xs" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-flask-vial fs-6"></i>
                    </div>
                    <h1 class="h5 fw-bold text-slate-800 mb-0">Strength Units</h1>
                </div>
                <p class="text-muted small mb-0 mt-1">Manage standardized medicine strength and concentration units (e.g., mg, ml, g, mcg, IU, %) used across hospital inventory.</p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#addStrengthUnitModal"
                        class="btn btn-primary rounded-xl px-3.5 py-2 text-xs fw-semibold shadow-xs d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add Strength Unit</span>
                </button>
            </div>
        </div>

        <!-- Metric Badges Row -->
        <div class="row g-2 mt-2">
            <div class="col-6 col-md-auto">
                <div class="border rounded-xl px-3 py-2 bg-white d-flex align-items-center gap-2 border-primary ring-1 ring-primary">
                    <span class="text-xs fw-semibold text-slate-500">Total Strength Units:</span>
                    <span class="badge bg-blue-100 text-primary rounded-pill">{{ $counts['total'] ?? $strengthUnits->count() }}</span>
                </div>
            </div>

            <div class="col-6 col-md-auto">
                <div class="border rounded-xl px-3 py-2 bg-white d-flex align-items-center gap-2">
                    <span class="text-xs fw-semibold text-slate-500">Assigned to Medicines:</span>
                    <span class="badge bg-green-100 text-green-800 rounded-pill">{{ $counts['in_use'] ?? 0 }}</span>
                </div>
            </div>

            <div class="col-6 col-md-auto">
                <div class="border rounded-xl px-3 py-2 bg-white d-flex align-items-center gap-2">
                    <span class="text-xs fw-semibold text-slate-500">Unassigned:</span>
                    <span class="badge bg-slate-200 text-slate-700 rounded-pill">{{ $counts['unused'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Master Data Sub-Navigation & Search Controls Bar -->
        <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-3 mt-3 pt-2 border-top">
            <!-- Master Data Tab Pills -->
            <div class="d-flex flex-wrap gap-1.5">
                <a href="{{ route('form-types.index') }}"
                    class="btn btn-sm btn-light border text-slate-600 rounded-pill px-3.5 py-1 fw-semibold text-xs d-inline-flex align-items-center gap-1.5">
                    <i class="fa-solid fa-capsules"></i>
                    <span>Form Types</span>
                </a>

                <a href="{{ route('units.index') }}"
                    class="btn btn-sm btn-light border text-slate-600 rounded-pill px-3.5 py-1 fw-semibold text-xs d-inline-flex align-items-center gap-1.5">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>Units</span>
                </a>

                <a href="{{ route('strength-units.index') }}"
                    class="btn btn-sm btn-primary rounded-pill px-3.5 py-1 fw-semibold text-xs d-inline-flex align-items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-flask-vial"></i>
                    <span>Strength Units</span>
                </a>

                <a href="{{ route('suppliers.index') }}"
                    class="btn btn-sm btn-light border text-slate-600 rounded-pill px-3.5 py-1 fw-semibold text-xs d-inline-flex align-items-center gap-1.5">
                    <i class="fa-solid fa-truck-medical"></i>
                    <span>Suppliers</span>
                </a>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('strength-units.index') }}" class="d-flex align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width: 280px;">
                    <span class="input-group-text bg-white border-end-0 text-slate-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search strength units..." class="form-control border-start-0 ps-0 text-xs">
                </div>

                <button type="submit" class="btn btn-sm btn-outline-primary rounded-lg text-xs px-3">
                    Search
                </button>

                @if(request('search'))
                <a href="{{ route('strength-units.index') }}" class="btn btn-sm btn-outline-secondary rounded-lg text-xs" title="Reset Search">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Strength Units Data Table Container -->
    <div class="flex-grow-1 overflow-auto p-4">
        <div class="card border-0 shadow-sm rounded-2xl overflow-hidden bg-white">
            <div class="data-log-header">
                <div class="d-flex align-items-center gap-2">
                    <h2 class="data-log-title">Registered Strength Units</h2>
                    <span class="badge bg-slate-100 text-slate-600 border rounded-pill text-xs px-2.5 py-1">
                        {{ $strengthUnits->count() }} {{ Str::plural('Entry', $strengthUnits->count()) }}
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table modern-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 100px;">ID</th>
                            <th scope="col">Strength Unit</th>
                            <th scope="col" style="width: 220px;">Linked Medicines</th>
                            <th scope="col" style="width: 160px;">Status</th>
                            <th scope="col" style="width: 180px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($strengthUnits as $strengthUnit)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td>
                                <span class="badge bg-blue-50 text-primary border border-blue-100 fw-bold px-2.5 py-1.5 rounded-lg" style="font-size: 0.775rem;">
                                    #{{ str_pad($strengthUnit->id, 3, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-lg bg-slate-100 text-slate-600 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fa-solid fa-flask-vial text-primary" style="font-size: 0.85rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-slate-900" style="font-size: 0.9rem;">
                                            {{ $strengthUnit->name }}
                                        </div>
                                        <small class="text-muted" style="font-size: 0.725rem;">Dosage concentration / strength unit</small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @php
                                    $medCount = (int) ($strengthUnit->medicines_count ?? 0);
                                @endphp
                                @if($medCount > 0)
                                    <span class="badge bg-blue-50 text-blue-700 border border-blue-100 rounded-pill px-3 py-1.5 text-xs fw-semibold">
                                        <i class="fa-solid fa-prescription-bottle-medical me-1"></i>
                                        {{ $medCount }} {{ Str::plural('Medicine', $medCount) }}
                                    </span>
                                @else
                                    <span class="text-muted text-xs">No linked medicines</span>
                                @endif
                            </td>

                            <td>
                                @if(($strengthUnit->medicines_count ?? 0) > 0)
                                    <span class="badge bg-green-100 text-green-800 border border-green-200 rounded-pill px-2.5 py-1 text-xs fw-semibold">
                                        <i class="fa-solid fa-check-circle me-1"></i>In Use
                                    </span>
                                @else
                                    <span class="badge bg-slate-100 text-slate-600 border rounded-pill px-2.5 py-1 text-xs fw-semibold">
                                        Available
                                    </span>
                                @endif
                            </td>

                            <td class="text-end">
                                <div class="d-inline-flex align-items-center justify-content-end gap-1.5">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary rounded-lg text-xs px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1 edit-strength-unit-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editStrengthUnitModal"
                                            data-id="{{ $strengthUnit->id }}"
                                            data-name="{{ $strengthUnit->name }}"
                                            data-med-count="{{ $strengthUnit->medicines_count ?? 0 }}"
                                            data-update-url="{{ route('strength-units.update', $strengthUnit->id) }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span>Edit</span>
                                    </button>

                                    <form action="{{ route('strength-units.destroy', $strengthUnit->id) }}"
                                          method="POST"
                                          class="delete-master-form d-inline-block"
                                          data-entry-name="{{ $strengthUnit->name }}"
                                          data-entry-type="Strength Unit">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger rounded-lg text-xs px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1">
                                            <i class="fa-solid fa-trash-can"></i>
                                            <span>Delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted d-flex flex-column align-items-center">
                                    <div class="rounded-circle bg-slate-100 p-3 mb-2">
                                        <i class="fa-solid fa-flask-vial fs-2 text-slate-400"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1 text-slate-700">No Strength Units Found</h6>
                                    <p class="text-muted small mb-3">No strength units match your current search or none have been added yet.</p>
                                    <button type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#addStrengthUnitModal"
                                            class="btn btn-sm btn-primary rounded-xl px-3.5 py-2 text-xs fw-semibold">
                                        <i class="fa-solid fa-plus me-1"></i>Add First Strength Unit
                                    </button>
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

<!-- Add Strength Unit Modal -->
<div class="modal fade" id="addStrengthUnitModal" tabindex="-1" aria-labelledby="addStrengthUnitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content overflow-hidden">
            <div class="gradient-header-card mb-0 rounded-0" style="padding: 1.25rem 1.5rem;">
                <div>
                    <h5 class="fw-bold text-white mb-1" id="addStrengthUnitModalLabel">
                        <i class="fa-solid fa-plus-circle me-2"></i>Add New Strength Unit
                    </h5>
                    <p class="text-white text-opacity-75 small mb-0">
                        Register a dosage strength or concentration unit (e.g., mg, ml, g, mcg, IU, %).
                    </p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('strength-units.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="add_strength_unit_name" class="form-label fw-bold text-slate-700">
                            Strength Unit Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="add_strength_unit_name"
                               value="{{ old('name') }}"
                               placeholder="e.g. mg, ml, g, mcg, IU, %..."
                               class="form-control"
                               maxlength="20"
                               required>
                        <small class="text-muted d-block mt-1.5" style="font-size: 0.775rem;">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i>
                            This unit will appear in the Strength dropdown when adding or editing medicines.
                        </small>
                    </div>

                    <!-- Quick Suggestions -->
                    <div class="bg-slate-50 border rounded-xl p-3">
                        <div class="text-xs fw-bold text-slate-500 text-uppercase mb-2" style="letter-spacing: 0.05em;">
                            Common Strength Units (Click to fill)
                        </div>
                        <div class="d-flex flex-wrap gap-1.5">
                            @foreach(['mg', 'ml', 'g', 'mcg', 'IU', '%', 'mg/ml', 'mcg/ml', 'mEq'] as $suggestion)
                                <button type="button"
                                        class="btn btn-sm btn-white bg-white border text-slate-600 rounded-pill px-2.5 py-1 text-xs fw-semibold hover:bg-blue-50 hover:text-primary"
                                        onclick="document.getElementById('add_strength_unit_name').value = '{{ $suggestion }}'; document.getElementById('add_strength_unit_name').focus();">
                                    {{ $suggestion }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-slate-50">
                    <button type="button" class="btn btn-outline-secondary rounded-xl px-4 py-2 text-xs fw-semibold" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary rounded-xl px-4 py-2 text-xs fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Save Strength Unit</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Strength Unit Modal -->
<div class="modal fade" id="editStrengthUnitModal" tabindex="-1" aria-labelledby="editStrengthUnitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content overflow-hidden">
            <div class="gradient-header-card mb-0 rounded-0" style="padding: 1.25rem 1.5rem;">
                <div>
                    <h5 class="fw-bold text-white mb-1" id="editStrengthUnitModalLabel">
                        <i class="fa-solid fa-pen-to-square me-2"></i>Edit Strength Unit
                    </h5>
                    <p class="text-white text-opacity-75 small mb-0" id="editStrengthUnitSubtitle">
                        Update the strength unit details.
                    </p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" id="editStrengthUnitForm" action="#">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-2">
                        <label for="edit_strength_unit_name" class="form-label fw-bold text-slate-700">
                            Strength Unit Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="edit_strength_unit_name"
                               placeholder="e.g. mg, ml, g, mcg, IU, %..."
                               class="form-control"
                               maxlength="20"
                               required>
                        <small class="text-muted d-block mt-1.5" style="font-size: 0.775rem;">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i>
                            Updating this unit name will automatically reflect across all linked medicines.
                        </small>
                    </div>
                </div>

                <div class="modal-footer bg-slate-50">
                    <button type="button" class="btn btn-outline-secondary rounded-xl px-4 py-2 text-xs fw-semibold" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary rounded-xl px-4 py-2 text-xs fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Update Strength Unit</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Populate Edit Modal when triggered
    document.querySelectorAll('.edit-strength-unit-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const id = button.getAttribute('data-id') || '';
            const name = button.getAttribute('data-name') || '';
            const medCount = button.getAttribute('data-med-count') || '0';
            const updateUrl = button.getAttribute('data-update-url') || '#';

            const form = document.getElementById('editStrengthUnitForm');
            const nameInput = document.getElementById('edit_strength_unit_name');
            const subtitle = document.getElementById('editStrengthUnitSubtitle');

            if (form) form.action = updateUrl;
            if (nameInput) nameInput.value = name;
            if (subtitle) {
                const paddedId = String(id).padStart(3, '0');
                subtitle.textContent = `Record ID: #${paddedId} • Linked to ${medCount} medicine(s)`;
            }
        });
    });

    // SweetAlert2 Delete Confirmation
    document.querySelectorAll('.delete-master-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const entryName = form.getAttribute('data-entry-name') || 'this entry';
            const entryType = form.getAttribute('data-entry-type') || 'entry';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete ' + entryType + '?',
                    html: `Are you sure you want to delete <span class="badge bg-blue-50 text-primary border border-blue-200 fw-bold px-2 py-1 rounded">${entryName}</span>?<div class="text-muted small mt-2">This action cannot be undone. Entries assigned to existing medicines are protected from deletion.</div>`,
                    icon: 'warning',
                    iconColor: '#dc2626',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa-solid fa-trash-can me-1"></i> Yes, delete it',
                    cancelButtonText: 'Cancel',
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
            } else if (confirm('Are you sure you want to delete ' + entryName + '?')) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
@endsection

