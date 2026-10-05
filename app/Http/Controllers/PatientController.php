<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Models\Admission;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    /**
     * Display the narcotic patient administration page.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $selectedId = $request->query('selected_patient');

        // Query patients who have admissions/narcotic dispensations
        $patientsQuery = Patient::query()
            ->with([
                'currentAdmission.ward',
                'admissions.ward',
                'admissions.dispensations' => function ($q) {
                    $q->with(['batch.medicine.unit', 'batch.medicine.form', 'issuedBy', 'witnessedBy'])
                      ->orderBy('date', 'desc');
                },
            ]);

        if ($search) {
            $patientsQuery->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhereHas('admissions', function ($sub) use ($search) {
                      $sub->where('bht_no', 'like', "%{$search}%");
                  });
            });
        }

        $patients = $patientsQuery->orderBy('patient_name')->get();

        // Determine currently selected patient
        $selectedPatient = null;
        if ($selectedId) {
            $selectedPatient = $patients->firstWhere('id', $selectedId);
        }
        if (!$selectedPatient && $patients->isNotEmpty()) {
            $selectedPatient = $patients->first();
        }

        // Gather all dispensations for selected patient across all admissions
        $dispensations = collect();
        if ($selectedPatient) {
            $dispensations = $selectedPatient->admissions->flatMap(function ($admission) {
                return $admission->dispensations->map(function ($d) use ($admission) {
                    $d->admission_info = $admission;
                    return $d;
                });
            })->sortByDesc('date');

            // Apply date / keyword filters if provided
            $filterDate = $request->query('filter_date');
            $filterSearch = $request->query('filter_search');

            if ($filterDate) {
                $dispensations = $dispensations->filter(function ($d) use ($filterDate) {
                    return $d->date?->format('Y-m-d') === $filterDate;
                });
            }

            if ($filterSearch) {
                $dispensations = $dispensations->filter(function ($d) use ($filterSearch) {
                    $drugName = $d->batch?->medicine?->name ?? '';
                    $issuer = $d->issuedBy?->name ?? '';
                    $witness = $d->witnessedBy?->name ?? '';
                    return str_contains(strtolower($drugName), strtolower($filterSearch)) ||
                           str_contains(strtolower($issuer), strtolower($filterSearch)) ||
                           str_contains(strtolower($witness), strtolower($filterSearch));
                });
            }
        }

        // Controlled medicines only for narcotic administration
        $controlledMedicines = Medicine::controlled()
            ->with(['batches' => function ($q) {
                $q->where('expiry_date', '>=', now()->toDateString())->orderBy('expiry_date', 'asc');
            }, 'unit', 'form'])
            ->orderBy('name')
            ->get();

        $wards = Ward::orderBy('ward_number')->get();
        $users = User::orderBy('name')->get();

        return view('patients.index', compact(
            'patients',
            'selectedPatient',
            'dispensations',
            'controlledMedicines',
            'wards',
            'users'
        ));
    }

    /**
     * Store a new patient and optional initial admission.
     */
    public function store(StorePatientRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $patient = Patient::create([
            'patient_name' => $validated['patient_name'],
            'created_at' => now(),
        ]);

        if (!empty($validated['bht_no']) && !empty($validated['ward_id'])) {
            Admission::create([
                'patient_id' => $patient->id,
                'bht_no' => $validated['bht_no'],
                'ward_id' => $validated['ward_id'],
                'admit_date' => $validated['admit_date'] ?? now(),
                'status' => 'Admitted',
            ]);
        }

        return redirect()
            ->route('patients.index', ['selected_patient' => $patient->id])
            ->with('success', "Patient '{$patient->patient_name}' registered successfully.");
    }

    /**
     * Show a patient (redirect to index with selected_patient param).
     */
    public function show(Patient $patient): RedirectResponse
    {
        return redirect()->route('patients.index', ['selected_patient' => $patient->id]);
    }
}
