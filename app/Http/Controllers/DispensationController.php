<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDispensationRequest;
use App\Models\Admission;
use App\Models\Dispensation;
use App\Models\MedicineBatch;
use Illuminate\Http\RedirectResponse;

class DispensationController extends Controller
{
    /**
     * Store a narcotic dispensation for an admitted patient.
     * Restricted to medicines with is_controlled = 1.
     * The DB trigger (trg_dispensation_ledger) automatically records this in stock_ledger.
     */
    public function store(StoreDispensationRequest $request, Admission $admission): RedirectResponse
    {
        $validated = $request->validated();

        $batch = MedicineBatch::with('medicine')->findOrFail($validated['batch_id']);

        if (!$batch->medicine || !$batch->medicine->is_controlled) {
            return back()->with('error', 'Dispensation rejected: Selected drug is not flagged as a controlled narcotic substance.');
        }

        Dispensation::create([
            'admission_id' => $admission->id,
            'batch_id' => $batch->id,
            'date' => $validated['date'],
            'qty_given' => $validated['qty_given'],
            'dosage' => $validated['dosage'],
            'usage_time' => $validated['usage_time'] ?? null,
            'issued_by' => $validated['issued_by'],
            'witnessed_by' => $validated['witnessed_by'] ?? null,
        ]);

        return redirect()
            ->route('patients.index', [
                'selected_patient' => $admission->patient_id,
            ])
            ->with('success', "Narcotic dispensation ({$batch->medicine->name} - Batch: {$batch->batch_no}) successfully logged.");
    }
}
