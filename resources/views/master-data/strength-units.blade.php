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
                        <i class="fa-solid fa-flask fs-6"></i>
                    </div>
                    <h1 class="h5 fw-bold text-slate-800 mb-0">Strength Units</h1>
                </div>
                <p class="text-muted small mb-0 mt-1">Manage standardized medicine strength measurement units (e.g., mg, ml, g, mcg, IU, %, mEq) across hospital inventory.</p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="button"
                        class="btn btn-primary rounded-xl px-3.5 py-2 text-xs fw-semibold shadow-xs d-inline-flex align-items-center gap-2"
                        data-bs-toggle="modal"
                        data-bs-target="#addStrengthUnitModal">
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
                    <i class="fa-solid fa-flask"></i>
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
                            <th scope="col">Strength Unit Name</th>
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
                                        <i class="fa-solid fa-flask text-primary" style="font-size: 0.85rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-slate-900" style="font-size: 0.9rem;">
                                            {{ $strengthUnit->name }}
                                        </div>
                                        <small class="text-muted" style="font-size: 0.725rem;">Medicine strength unit</small>
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
                                            class="btn btn-sm btn-outline-primary rounded-lg text-xs px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editStrengthUnitModal-{{ $strengthUnit->id }}">
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
                                        <i class="fa-solid fa-flask fs-2 text-slate-400"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1 text-slate-700">No Strength Units Found</h6>
                                    <p class="text-muted small mb-3">No strength units match your current search or none have been added yet.</p>
                                    <button type="button"
                                            class="btn btn-sm btn-primary rounded-xl px-3.5 py-2 text-xs fw-semibold"
                                            data-bs-toggle="modal"
                                            data-bs-target="#addStrengthUnitModal">
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
        <div class="modal-content border-0 shadow-lg rounded-2xl overflow-hidden">
            <div class="modal-header bg-white border-bottom px-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-lg bg-blue-50 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-flask"></i>
                    </div>
                    <h5 class="modal-title fw-bold text-slate-800 fs-6" id="addStrengthUnitModalLabel">Add Strength Unit</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('strength-units.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-2">
                        <label for="strengthUnitName" class="form-label text-xs fw-bold text-slate-600 text-uppercase">Strength Unit Name <span class="text-danger">*</span></label>
                        <input type="text"
                               id="strengthUnitName"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="e.g. mg, ml, g, mcg, IU"
                               required
                               maxlength="50"
                               class="form-control rounded-xl bg-slate-50 border-slate-200 text-sm p-2.5">
                        <small class="text-muted text-xs mt-1 d-block">Enter a standardized measurement unit used for medicine dosage strength.</small>
                    </div>
                </div>
                <div class="modal-footer bg-slate-50 border-top px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-xl px-3.5 py-2 text-xs fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-3.5 py-2 text-xs fw-semibold shadow-xs">
                        <i class="fa-solid fa-check me-1"></i>Save Strength Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Strength Unit Modals -->
@foreach($strengthUnits as $strengthUnit)
<div class="modal fade" id="editStrengthUnitModal-{{ $strengthUnit->id }}" tabindex="-1" aria-labelledby="editStrengthUnitModalLabel-{{ $strengthUnit->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-2xl overflow-hidden">
            <div class="modal-header bg-white border-bottom px-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-lg bg-blue-50 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <h5 class="modal-title fw-bold text-slate-800 fs-6" id="editStrengthUnitModalLabel-{{ $strengthUnit->id }}">Edit Strength Unit</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('strength-units.update', $strengthUnit->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-2">
                        <label for="strengthUnitName-{{ $strengthUnit->id }}" class="form-label text-xs fw-bold text-slate-600 text-uppercase">Strength Unit Name <span class="text-danger">*</span></label>
                        <input type="text"
                               id="strengthUnitName-{{ $strengthUnit->id }}"
                               name="name"
                               value="{{ $strengthUnit->name }}"
                               required
                               maxlength="50"
                               class="form-control rounded-xl bg-slate-50 border-slate-200 text-sm p-2.5">
                    </div>
                </div>
                <div class="modal-footer bg-slate-50 border-top px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-xl px-3.5 py-2 text-xs fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-3.5 py-2 text-xs fw-semibold shadow-xs">
                        <i class="fa-solid fa-check me-1"></i>Update Strength Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
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
