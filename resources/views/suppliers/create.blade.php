@extends('layouts.app')

@section('title', 'Add Supplier - Master Data')

@section('content')
<div class="d-flex flex-column flex-grow-1 overflow-hidden bg-slate-50 w-100">

    <!-- Top Header Bar -->
    <div class="bg-white border-bottom px-4 py-3 shadow-2xs">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-blue-50 text-primary p-2 d-flex align-items-center justify-content-center shadow-xs" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-truck-medical fs-6"></i>
                    </div>
                    <h1 class="h5 fw-bold text-slate-800 mb-0">Add New Supplier</h1>
                </div>
                <p class="text-muted small mb-0 mt-1">Register a new pharmaceutical supplier or vendor for hospital inventory orders and stock receipts.</p>
            </div>

            <div>
                <a href="{{ route('suppliers.index') }}" class="btn btn-light border text-slate-600 rounded-xl px-3.5 py-2 text-xs fw-semibold d-inline-flex align-items-center gap-2 shadow-2xs">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Back to Suppliers</span>
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
                            <i class="fa-solid fa-plus-circle me-2"></i>Create Pharmaceutical Supplier
                        </h2>
                        <p class="text-white text-opacity-75 small mb-0">
                            Enter the official supplier organization name and contact details.
                        </p>
                    </div>
                </div>

                <!-- Form Body -->
                <form method="POST" action="{{ route('suppliers.store') }}" class="p-4 p-md-5">
                    @csrf

                    <div class="mb-4">
                        <label for="supplier_name" class="form-label fw-bold text-slate-700">
                            Supplier Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="supplier_name"
                               id="supplier_name"
                               value="{{ old('supplier_name') }}"
                               placeholder="e.g. State Pharmaceuticals Corporation (SPC)"
                               class="form-control @error('supplier_name') is-invalid @enderror"
                               maxlength="150"
                               required
                               autofocus>
                        @error('supplier_name')
                            <div class="invalid-feedback fw-semibold">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="contact_info" class="form-label fw-bold text-slate-700">
                            Contact Information / Address
                        </label>
                        <input type="text"
                               name="contact_info"
                               id="contact_info"
                               value="{{ old('contact_info') }}"
                               placeholder="e.g. Colombo, Sri Lanka - Tel: 011-2334455"
                               class="form-control @error('contact_info') is-invalid @enderror"
                               maxlength="255">
                        @error('contact_info')
                            <div class="invalid-feedback fw-semibold">
                                {{ $message }}
                            </div>
                        @enderror
                        <small class="text-muted d-block mt-1.5" style="font-size: 0.775rem;">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i>
                            This supplier will be selectable when placing orders and confirming stock receipts.
                        </small>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary rounded-xl px-4 py-2 text-xs fw-semibold">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary rounded-xl px-4 py-2 text-xs fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-check"></i>
                            <span>Save Supplier</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

