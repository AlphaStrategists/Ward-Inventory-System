@extends('layouts.app')

@section('title', 'Add Unit - Master Data')

@section('content')
<div class="d-flex flex-column flex-grow-1 overflow-hidden bg-slate-50 w-100">

    <!-- Top Header Bar -->
    <div class="bg-white border-bottom px-4 py-3 shadow-2xs">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-blue-50 text-primary p-2 d-flex align-items-center justify-content-center shadow-xs" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-scale-balanced fs-6"></i>
                    </div>
                    <h1 class="h5 fw-bold text-slate-800 mb-0">Add New Measurement Unit</h1>
                </div>
                <p class="text-muted small mb-0 mt-1">Register a new measurement or dispensing unit for hospital medicine inventory records.</p>
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
                            <i class="fa-solid fa-plus-circle me-2"></i>Create Measurement Unit
                        </h2>
                        <p class="text-white text-opacity-75 small mb-0">
                            Enter a unique measurement unit name (e.g., mg, ml, g, Tablet, Capsule, Vial, Ampoule).
                        </p>
                    </div>
                </div>

                <!-- Form Body -->
                <form method="POST" action="{{ route('units.store') }}" class="p-4 p-md-5">
                    @csrf

                    <div class="mb-4">
                        <label for="unit_name" class="form-label fw-bold text-slate-700">
                            Unit Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="unit_name"
                               id="unit_name"
                               value="{{ old('unit_name') }}"
                               placeholder="e.g. mg, ml, g, Tablet, Vial, Ampoule, Bottle..."
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
                            This measurement unit will be selectable when adding or editing medicines in the ward inventory.
                        </small>
                    </div>

                    <!-- Quick Suggestions -->
                    <div class="bg-slate-50 border rounded-xl p-3 mb-4">
                        <div class="text-xs fw-bold text-slate-500 text-uppercase mb-2" style="letter-spacing: 0.05em;">
                            Common Measurement Units (Click to fill)
                        </div>
                        <div class="d-flex flex-wrap gap-1.5">
                            @foreach(['mg', 'ml', 'g', 'mcg', 'IU', 'Tablet', 'Capsule', 'Vial', 'Ampoule', 'Bottle', 'Tube', 'Sachet', 'Piece'] as $suggestion)
                                <button type="button"
                                        class="btn btn-sm btn-white bg-white border text-slate-600 rounded-pill px-2.5 py-1 text-xs fw-semibold hover:bg-blue-50 hover:text-primary"
                                        onclick="document.getElementById('unit_name').value = '{{ $suggestion }}'; document.getElementById('unit_name').focus();">
                                    {{ $suggestion }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('units.index') }}" class="btn btn-outline-secondary rounded-xl px-4 py-2 text-xs fw-semibold">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary rounded-xl px-4 py-2 text-xs fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-check"></i>
                            <span>Save Unit</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
