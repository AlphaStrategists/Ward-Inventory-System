<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\NarcoticUsage;
use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Http\Request;

class NarcoticUsageController extends Controller
{
    public function index(Request $request)
    {
        $query = NarcoticUsage::with(['item', 'patient', 'recordedBy']);

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        $usages = $query->orderBy('usage_id', 'desc')->get();
        $narcoticItems = Item::where('workflow_pattern', 'narcotic_direct')->get();
        $patients = Patient::orderBy('name')->get();
        $staffList = Staff::orderBy('name')->get();

        return view('narcotic-usage.index', compact('usages', 'narcoticItems', 'patients', 'staffList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,item_id',
            'patient_id' => 'nullable|exists:patients,patient_id',
            'patient_name' => 'required_if:patient_id,null|nullable|string|max:100',
            'patient_nic' => 'nullable|string|max:20',
            'patient_bht' => 'nullable|string|max:30',
            'bed_no' => 'nullable|string|max:20',
            'usage_date' => 'required|date',
            'usage_time' => 'required',
            'dosage' => 'required|string|max:50',
            'recorded_by_staff_id' => 'required|exists:staff,staff_id',
        ]);

        $item = Item::findOrFail($validated['item_id']);

        if ($item->workflow_pattern !== 'narcotic_direct') {
            return redirect()->back()->withErrors(['item_id' => 'Selected item is not a narcotic direct usage item.']);
        }

        // If patient_id is not provided, create a new patient
        $patientId = $validated['patient_id'] ?? null;
        if (!$patientId && !empty($validated['patient_name'])) {
            $patient = Patient::create([
                'name' => $validated['patient_name'],
                'nic' => $validated['patient_nic'] ?? null,
                'bht' => $validated['patient_bht'] ?? null,
            ]);
            $patientId = $patient->patient_id;
        }

        NarcoticUsage::create([
            'item_id' => $item->item_id,
            'patient_id' => $patientId,
            'bed_no' => $validated['bed_no'] ?? null,
            'usage_date' => $validated['usage_date'],
            'usage_time' => $validated['usage_time'],
            'dosage' => $validated['dosage'],
            'recorded_by_staff_id' => $validated['recorded_by_staff_id'],
        ]);

        // Decrement item quantity if available
        if ($item->quantity > 0) {
            $item->decrement('quantity', 1);
        }

        return redirect()->back()->with('success', 'Narcotic administration logged successfully.');
    }
}
