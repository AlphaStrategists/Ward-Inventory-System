@extends('layouts.app')

@section('title', ' Patient Administration and Narcotic Dispensation - Ward 48')

@section('content')

    <!-- Left Sidebar: Patients List (Only patients receiving narcotics) -->
    <x-inventory-sidebar 
        brandTitle="Narcotics Registry"
        brandSubtitle="CONTROLLED DRUG RECIPIENTS"
        searchPlaceholder="Search by patient name or BHT..."
        :searchValue="request('search', '')"
        addButtonLabel="New Patient"
        addModalId="createPatientModal"
        :showAddButton="auth()->user()->hasRole('Staff Nurse')"
    >
    
        @forelse($patients as $p)
            @php
                $isActive = $selectedPatient && $selectedPatient->id === $p->id;
                $activeAdmission = $p->currentAdmission ?? $p->admissions->first();
                $status = $activeAdmission?->status ?? 'Registered';
                $statusColors = [
                    'Admitted' => 'bg-success-subtle text-success border-success-subtle',
                    'Discharged' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                    'Transferred' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                ];
            @endphp
            <div class="sidebar-item-card {{ $isActive ? 'active' : '' }}">
                <div class="d-flex align-items-center justify-content-between">
                    <a href="{{ route('patients.index', array_merge(request()->query(), ['selected_patient' => $p->id])) }}" 
                       class="text-decoration-none flex-grow-1">
                        <div class="sidebar-item-name">{{ $p->patient_name }}</div>
                        <div class="d-flex align-items-center gap-1">
                            @if($activeAdmission)
                                <small class="text-primary fw-semibold" style="font-size: 0.725rem;">
                                    <i class="bi bi-person-vcard me-1"></i>{{ $activeAdmission->bht_no }}
                                </small>
                                <small class="text-muted" style="font-size: 0.725rem;">
                                    • {{ $activeAdmission->ward?->ward_number ?? 'Ward 48' }}
                                </small>
                            @else
                                <small class="text-muted" style="font-size: 0.725rem;">No active admission</small>
                            @endif
                        </div>
                    </a>

                    <div class="text-end">
                        <span class="badge border {{ $statusColors[$status] ?? 'bg-light text-dark' }}" style="font-size: 0.7rem;">
                            {{ $status }}
                        </span>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mt-2 pt-1 border-top border-light-subtle">
                                        <div class="sidebar-item-actions">
                        @if($activeAdmission && auth()->user()->hasRole('Staff Nurse'))
                            <button type="button" 
                                    class="action-icon-btn" 
                                    title="Update Admission Status" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editAdmissionStatusModal{{ $activeAdmission->id }}">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        @endif
                        <a href="{{ route('patients.index', ['selected_patient' => $p->id]) }}" 
                           class="action-icon-btn" 
                           title="View History">
                            <i class="bi bi-clock-history"></i>
                        </a>
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem;">
                        {{ $p->admissions->flatMap->dispensations->count() }} narcotic doses
                    </small>
                </div>
            </div>

            <!-- Modal: Edit Admission Status for Patient (nurse-only) -->
            @if($activeAdmission && auth()->user()->hasRole('Staff Nurse'))
                <div class="modal fade" id="editAdmissionStatusModal{{ $activeAdmission->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('admissions.update', $activeAdmission->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Update Admission Status</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Patient Name</label>
                                        <input type="text" class="form-control bg-light" value="{{ $p->patient_name }}" readonly>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">BHT Number</label>
                                            <input type="text" name="bht_no" class="form-control" value="{{ old('bht_no', $activeAdmission->bht_no) }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Ward</label>
                                            <select name="ward_id" class="form-select" required>
                                                @foreach($wards as $w)
                                                    <option value="{{ $w->id }}" {{ (old('ward_id', $activeAdmission->ward_id) == $w->id) ? 'selected' : '' }}>
                                                        {{ $w->ward_number }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Admission Date</label>
                                            <input type="date" name="admit_date" class="form-control" value="{{ $activeAdmission->admit_date?->format('Y-m-d') }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Current Status</label>
                                            <select name="status" class="form-select" required>
                                                <option value="Admitted" {{ $activeAdmission->status === 'Admitted' ? 'selected' : '' }}>Admitted</option>
                                                <option value="Discharged" {{ $activeAdmission->status === 'Discharged' ? 'selected' : '' }}>Discharged</option>
                                                <option value="Transferred" {{ $activeAdmission->status === 'Transferred' ? 'selected' : '' }}>Transferred</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Status</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                <div class="fw-semibold">No patients enrolled</div>
                <small>Click "New Patient" to admit a patient for controlled drug therapy.</small>
            </div>
        @endforelse
    </x-inventory-sidebar>

    <!-- Main Content Panel -->
    <main class="main-content-panel">
        @if($selectedPatient)
            @php
                $currAdm = $selectedPatient->currentAdmission ?? $selectedPatient->admissions->first();
                $admStatus = $currAdm?->status ?? 'Not Admitted';
                $isAdmitted = $admStatus === 'Admitted';
            @endphp

            <!-- Top Gradient Header Banner -->
            <x-header-card 
                :title="$selectedPatient->patient_name"
                :subtitle="'BHT: ' . ($currAdm?->bht_no ?? 'N/A') . ' • ' . ('Ward 48') . ' • Admitted: ' . ($currAdm?->admit_date?->format('M d, Y') ?? 'N/A')"
                :metricValue="strtoupper($admStatus)"
                metricLabel="PATIENT STATUS"
                :metricColor="$isAdmitted ? '#16a34a' : '#64748b'"
            />

            <!-- Subnav Tabs & Filter Bar -->
            <div class="subnav-filter-bar">
                <div class="custom-nav-tabs">
                    <button class="custom-nav-tab-btn active" id="btnTabDisp" onclick="switchPatientTab('dispensationsTab')">
                        <i class="bi bi-capsule me-1"></i>
                        Narcotic Administrations ({{ $dispensations->count() }})
                    </button>
                    <button class="custom-nav-tab-btn" id="btnTabAdm" onclick="switchPatientTab('admissionsTab')">
                        <i class="bi bi-clipboard2-pulse me-1"></i>
                        Admissions History ({{ $selectedPatient->admissions->count() }})
                    </button>
                </div>

                <!-- Filters -->
                <form method="GET" action="{{ route('patients.index') }}" class="filter-controls-group">
                    <input type="hidden" name="selected_patient" value="{{ $selectedPatient->id }}">
                    
                    <div class="d-flex align-items-center">
                        <i class="bi bi-search text-muted me-1" style="font-size: 0.8rem;"></i>
                        <input type="text" 
                               name="filter_search" 
                               value="{{ request('filter_search') }}" 
                               class="filter-search-input" 
                               placeholder="Search drug, officer...">
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
                        <a href="{{ route('patients.index', ['selected_patient' => $selectedPatient->id]) }}" 
                           class="filter-clear-btn">
                            ✕ Clear
                        </a>
                    @endif
                </form>
            </div>

            <!-- Tab 1: Narcotic Administration Log -->
            <div id="dispensationsTab">
                    <x-log-table 
                    title="Narcotic Administration Log (Controlled Substances)"
                    :actionModalId="null"
                >
                    <x-slot:headers>
                        <th>Date & Time</th>
                        <th>Controlled Medicine</th>
                        <th>Batch No</th>
                        <th>Clinical Dosage</th>
                        <th>Qty Given</th>
                        <th>Issuing Officer</th>
                        <th>Witnessing Officer</th>
                    </x-slot:headers>

                    @forelse($dispensations as $disp)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $disp->date?->format('M d, Y') ?? 'N/A' }}</div>
                                <div class="d-flex align-items-center gap-1 text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock"></i>
                                    <span>{{ $disp->usage_time ?: ($disp->date?->format('h:i A') ?? 'N/A') }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">
                                    {{ $disp->batch?->medicine?->name ?? 'Controlled Drug' }}
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.65rem;">
                                        <i class="bi bi-shield-lock-fill me-1"></i>CONTROLLED
                                    </span>
                                    <small class="text-muted" style="font-size: 0.725rem;">
                                        {{ $disp->batch?->medicine?->strength ?? '' }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">
                                    {{ $disp->batch?->batch_no ?? 'BATCH-N/A' }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-primary-emphasis">{{ $disp->dosage }}</div>
                            </td>
                            <td>
                                <span class="badge bg-danger text-white fw-bold px-2 py-1">
                                    {{ $disp->qty_given }} {{ $disp->batch?->medicine?->unit?->unit_name ?? 'Ampoule' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.7rem;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <span class="fw-medium">{{ $disp->issuedBy?->name ?? 'Staff Nurse' }}</span>
                                </div>
                            </td>
                            <td>
                                @if($disp->witnessedBy)
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <span class="fw-medium">{{ $disp->witnessedBy->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">Unwitnessed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-capsule fs-1 d-block mb-2 text-danger-subtle"></i>
                                <div>No narcotic dispensations logged yet for <strong>{{ $selectedPatient->patient_name }}</strong>.</div>
                                <small class="text-muted">This log updates automatically once a narcotic drug entry is recorded for this patient.</small>
                            </td>
                        </tr>
                    @endforelse
                </x-log-table>
            </div>

            <!-- Tab 2: Admissions History -->
            <div id="admissionsTab" style="display: none;">
                    <x-log-table 
                    title="Patient Admission Records"
                    :actionModalId="auth()->user()->hasRole('Staff Nurse') ? 'addAdmissionModal' : null"
                    actionLabel="New Admission"
                >
                    <x-slot:headers>
                        <th>BHT Number</th>
                        <th>Ward</th>
                        <th>Admission Date</th>
                        <th>Status</th>
                        <th>Narcotics Received</th>
                        <th>Actions</th>
                    </x-slot:headers>

                    @foreach($selectedPatient->admissions as $adm)
                        <tr>
                            <td>
                                <div class="fw-bold text-primary">{{ $adm->bht_no }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $adm->ward?->ward_number }} 
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $adm->admit_date?->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $adm->admit_date?->format('h:i A') }}</small>
                            </td>
                            <td>
                                @php
                                    $admClasses = [
                                        'Admitted' => 'bg-success text-white',
                                        'Discharged' => 'bg-secondary text-white',
                                        'Transferred' => 'bg-warning text-dark',
                                    ];
                                @endphp
                                <span class="badge {{ $admClasses[$adm->status] ?? 'bg-light text-dark' }}">
                                    {{ $adm->status }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    {{ $adm->dispensations->count() }} dispensations
                                </span>
                            </td>
                            <td>
                                @if(auth()->user()->hasRole('Staff Nurse'))
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary rounded-pill px-3" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editAdmissionStatusModal{{ $adm->id }}">
                                        Update Status
                                    </button>
                                @else
                                    <span class="text-muted small">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-log-table>
            </div>



    <!-- Modal: Add New Admission for Existing Patient (nurse-only) -->
    @if(auth()->user()->hasRole('Staff Nurse'))
    <div class="modal fade" id="addAdmissionModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('patients.admissions.store', $selectedPatient->id) }}">
                            @csrf
                            <input type="hidden" name="patient_id" value="{{ $selectedPatient->id }}">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">New Ward Admission</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Patient</label>
                                    <input type="text" class="form-control bg-light" value="{{ $selectedPatient->patient_name }}" readonly>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">BHT Number</label>
                                        <input type="text" name="bht_no" class="form-control" placeholder="e.g. BHT-48-2026-0901" required>
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
                                        <label class="form-label">Admit Date</label>
                                        <input type="datetime-local" name="admit_date" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Initial Status</label>
                                        <select name="status" class="form-select" required>
                                            <option value="Admitted" selected>Admitted</option>
                                            <option value="Transferred">Transferred</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary rounded-pill px-4">Create Admission</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

        @else
            <div class="text-center py-5 my-5">
                <div class="bg-danger-subtle text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-shield-exclamation fs-1"></i>
                </div>
                <h3>No Narcotic Patients Enrolled</h3>
                @if(auth()->user()->hasRole('Staff Nurse'))
                    
                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2" data-bs-toggle="modal" data-bs-target="#createPatientModal">
                        <i class="bi bi-plus-lg me-1"></i> Enroll Patient
                </button>
                @else
                    <p class="text-muted">Only patients receiving controlled narcotics are registered here.</p>
                @endif
            </div>
        @endif
    </main>

    <!-- Modal: Enroll New Patient (nurse-only) -->
    @if(auth()->user()->hasRole('Staff Nurse'))
    <div class="modal fade" id="createPatientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('patients.store') }}">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold">Enroll Patient (Narcotic Dispensing)</h5>
                            <small class="text-muted">Register patient details and initial admission BHT</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Full Patient Name</label>
                            <input type="text" name="patient_name" class="form-control" placeholder="e.g. K. M. Bandara" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">BHT Number (Bed Head Ticket)</label>
                                <input type="text" name="bht_no" class="form-control" placeholder="e.g. BHT-48-2026-0899">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Admitted Ward</label>
                                <select name="ward_id" class="form-select">
                                    @foreach($wards as $w)
                                        <option value="{{ $w->id }}">{{ $w->ward_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Admit Date & Time</label>
                            <input type="datetime-local" name="admit_date" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                        </div>

                        
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Enroll Patient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif



@endsection

@push('scripts')
<script>
    function switchPatientTab(tabId) {
        document.getElementById('dispensationsTab').style.display = tabId === 'dispensationsTab' ? '' : 'none';
        document.getElementById('admissionsTab').style.display = tabId === 'admissionsTab' ? '' : 'none';
        
        document.getElementById('btnTabDisp').classList.toggle('active', tabId === 'dispensationsTab');
        document.getElementById('btnTabAdm').classList.toggle('active', tabId === 'admissionsTab');
    }
</script>
@endpush
