@extends('layouts.app')

@section('title', 'Edit Unit - Master Data')

@section('content')
<div class="d-flex flex-column flex-grow-1 overflow-hidden bg-slate-50 w-100">

    <!-- Top Header Bar -->
    <div class="bg-white border-bottom px-4 py-3 shadow-2xs">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-blue-50 text-primary p-2 d-flex align-items-center justify-content-center shadow-xs" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-pen-to-square fs-6"></i>
                    </div>
                    <h1 class="h5 fw-bold text-slate-800 mb-0">Edit Measurement Unit</h1>
                </div>
                <p class="text-muted small mb-0 mt-1">Update the measurement unit details for <strong>{{ $unit->unit_name }}</strong>.</p>
            </div>

            <div>
                <a href="{{ route('units.index') }}" class="btn btn-light border text-slate-600 rounded-xl px-3.5 py-2 text-xs fw-semibold d-inline-flex align-items-center gap-2 shadow-2xs">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Back to Units</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Form Content Area -->
    <div class="flex-grow-1 overflow-auto p-4">
        <div class="mx-auto" style="max-width: 680px;">
            <div class="card border-0 shadow-sm rounded-2xl overflow-hidden bg-white">
                <!-- Card Header Banner -->
                <div class="gradient-header-card mb-0 rounded-0" style="padding: 1.5rem 2rem;">
                    <div>
                        <h2 class="h5 fw-bold text-white mb-1">
                            <i class="fa-solid fa-scale-balanced me-2"></i>Update Measurement Unit
                        </h2>
                        <p class="text-white text-opacity-75 small mb-0">
                            Record ID: #{{ str_pad($unit->id, 3, '0', STR_PAD_LEFT) }} â€¢ Linked to {{ $unit->medicines_count ?? $unit->medicines()->count() }} medicine(s)
                        </p>
                    </div>
                </div>

                <!-- Form Body -->
                <form method="POST" action="{{ route('units.update', $unit->id) }}" class="p-4 p-md-5">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="unit_name" class="form-label fw-bold text-slate-700">
                            Unit Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="unit_name"
                               id="unit_name"
                               value="{{ old('unit_name', $unit->unit_name) }}"
                               placeholder="e.g. mg, ml, g, Tablet, Vial..."
                               class="form-control @error('unit_name') is-invalid @enderror"
                               maxlength="50"
                               required
                               autofocus>
                        @error('unit_name')
                            <div class="invalid-feedback fw-semibold">
                                {{ $message }}
                            </div>
                        @enderror
                        <small class="text-muted d-block mt-1.5" style="font-size: 0.775rem;">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i>
                            Updating this unit name will automatically reflect across all {{ $unit->medicines_count ?? 0 }} linked medicine(s).
                        </small>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('units.index') }}" class="btn btn-outline-secondary rounded-xl px-4 py-2 text-xs fw-semibold">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary rounded-xl px-4 py-2 text-xs fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Update Unit</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
