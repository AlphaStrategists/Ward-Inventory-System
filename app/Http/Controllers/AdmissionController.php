<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdmissionRequest;
use App\Http\Requests\UpdateAdmissionRequest;
use App\Models\Admission;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;

class AdmissionController extends Controller
{
    /**
     * Store a new admission record for a patient.
     */
    public function store(StoreAdmissionRequest $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validated();
        $validated['patient_id'] = $patient->id;

        $admission = Admission::create($validated);

        return redirect()
            ->route('patients.index', ['selected_patient' => $patient->id])
            ->with('success', "New admission (BHT: {$admission->bht_no}) created for {$patient->patient_name}.");
    }

    /**
     * Update an admission record (e.g. status change).
     */
    public function update(UpdateAdmissionRequest $request, Admission $admission): RedirectResponse
    {
        $admission->update($request->validated());

        return redirect()
            ->route('patients.index', ['selected_patient' => $admission->patient_id])
            ->with('success', "Admission BHT: {$admission->bht_no} status updated to {$admission->status}.");
    }
}
