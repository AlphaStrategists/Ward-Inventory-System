@extends('layouts.app')

@section('title', 'Form Types - Master Data')

@section('content')
<div class="d-flex flex-column flex-grow-1 overflow-hidden bg-slate-50 w-100">

    <!-- Top Action & Filter Header -->
    <div class="bg-white border-bottom px-4 py-3 shadow-2xs">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-blue-50 text-primary p-2 d-flex align-items-center justify-content-center shadow-xs" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-capsules fs-6"></i>
                    </div>
                    <h1 class="h5 fw-bold text-slate-800 mb-0">Medicine Form Types</h1>
                </div>
                <p class="text-muted small mb-0 mt-1">Manage standardized pharmaceutical dosage forms (e.g., Tablet, Capsule, Syrup, Injection) used across hospital inventory.</p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('form-types.create') }}" class="btn btn-primary rounded-xl px-3.5 py-2 text-xs fw-semibold shadow-xs d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add Form Type</span>
                </a>
            </div>
        </div>

        <!-- Metric Badges Row -->
        <div class="row g-2 mt-2">
            <div class="col-6 col-md-auto">
                <div class="border rounded-xl px-3 py-2 bg-white d-flex align-items-center gap-2 border-primary ring-1 ring-primary">
                    <span class="text-xs fw-semibold text-slate-500">Total Form Types:</span>
                    <span class="badge bg-blue-100 text-primary rounded-pill">{{ $counts['total'] ?? $formTypes->count() }}</span>
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
                    class="btn btn-sm btn-primary rounded-pill px-3.5 py-1 fw-semibold text-xs d-inline-flex align-items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-capsules"></i>
                    <span>Form Types</span>
                </a>

                <a href="{{ route('units.index') }}"
                    class="btn btn-sm btn-light border text-slate-600 rounded-pill px-3.5 py-1 fw-semibold text-xs d-inline-flex align-items-center gap-1.5">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>Units</span>
                </a>

                <a href="{{ route('suppliers.index') }}"
                    class="btn btn-sm btn-light border text-slate-600 rounded-pill px-3.5 py-1 fw-semibold text-xs d-inline-flex align-items-center gap-1.5">
                    <i class="fa-solid fa-truck-medical"></i>
                    <span>Suppliers</span>
                </a>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('form-types.index') }}" class="d-flex align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width: 280px;">
                    <span class="input-group-text bg-white border-end-0 text-slate-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search form types..." class="form-control border-start-0 ps-0 text-xs">
                </div>

                <button type="submit" class="btn btn-sm btn-outline-primary rounded-lg text-xs px-3">
                    Search
                </button>

                @if(request('search'))
                <a href="{{ route('form-types.index') }}" class="btn btn-sm btn-outline-secondary rounded-lg text-xs" title="Reset Search">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Form Types Data Table Container -->
    <div class="flex-grow-1 overflow-auto p-4">
        <div class="card border-0 shadow-sm rounded-2xl overflow-hidden bg-white">
            <div class="data-log-header">
                <div class="d-flex align-items-center gap-2">
                    <h2 class="data-log-title">Registered Dosage Form Types</h2>
                    <span class="badge bg-slate-100 text-slate-600 border rounded-pill text-xs px-2.5 py-1">
                        {{ $formTypes->count() }} {{ Str::plural('Entry', $formTypes->count()) }}
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table modern-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 100px;">ID</th>
                            <th scope="col">Form Type Name</th>
                            <th scope="col" style="width: 220px;">Linked Medicines</th>
                            <th scope="col" style="width: 160px;">Status</th>
                            <th scope="col" style="width: 180px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($formTypes as $formType)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td>
                                <span class="badge bg-blue-50 text-primary border border-blue-100 fw-bold px-2.5 py-1.5 rounded-lg" style="font-size: 0.775rem;">
                                    #{{ str_pad($formType->id, 3, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-lg bg-slate-100 text-slate-600 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fa-solid fa-pills text-primary" style="font-size: 0.85rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-slate-900" style="font-size: 0.9rem;">
                                            {{ $formType->form_name }}
                                        </div>
                                        <small class="text-muted" style="font-size: 0.725rem;">Pharmaceutical dosage form</small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @php
                                    $medCount = (int) ($formType->medicines_count ?? 0);
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
                                @if(($formType->medicines_count ?? 0) > 0)
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
                                    <a href="{{ route('form-types.edit', $formType->id) }}"
                                       class="btn btn-sm btn-outline-primary rounded-lg text-xs px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span>Edit</span>
                                    </a>

                                    <form action="{{ route('form-types.destroy', $formType->id) }}"
                                          method="POST"
                                          class="delete-master-form d-inline-block"
                                          data-entry-name="{{ $formType->form_name }}"
                                          data-entry-type="Form Type">
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
                                        <i class="fa-solid fa-capsules fs-2 text-slate-400"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1 text-slate-700">No Form Types Found</h6>
                                    <p class="text-muted small mb-3">No medicine dosage form types match your current search or none have been added yet.</p>
                                    <a href="{{ route('form-types.create') }}" class="btn btn-sm btn-primary rounded-xl px-3.5 py-2 text-xs fw-semibold">
                                        <i class="fa-solid fa-plus me-1"></i>Add First Form Type
                                    </a>
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
