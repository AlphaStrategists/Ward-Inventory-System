<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicine;
use App\Models\Category;
use App\Models\Unit;
use App\Models\MedicineForm;
use App\Models\MedicineBatch;
use App\Models\Dispensation;
use App\Models\Admission;
use App\Models\Order;
use App\Models\OrderDetail;

class MedicineController extends Controller
{
    public function index($category, Request $request)
    {
        $medicines = Medicine::with(['unit', 'form'])->whereHas('category', function($q) use ($category) {
            $q->where('name', $category);
        })->get();
        
        $firstMedicine = $medicines->first();

        if ($firstMedicine) {
            return redirect()->route('inventory.details', ['category' => $category, 'id' => $firstMedicine->id]);
        }

        $selectedMedicine = null;
        $pharmacyOrders = collect([]);
        $patientAdministrations = collect([]);

        return view('medicines.medicine-dashboard', compact('category', 'medicines', 'selectedMedicine', 'pharmacyOrders', 'patientAdministrations'));
    }

    public function getDetails($category, $id)
    {
        $medicines = Medicine::with(['unit', 'form'])->whereHas('category', function($q) use ($category) {
            $q->where('name', $category);
        })->get();
        
        $selectedMedicine = $medicines->firstWhere('id', $id) ?? $medicines->first();

        if (!$selectedMedicine) {
            return redirect()->route('inventory.index', ['category' => $category]);
        }

        // Fetch Pharmacy Orders using OrderDetail mapping
        $pharmacyOrders = OrderDetail::with(['order.requester', 'order.approver'])
            ->where('medicine_id', $selectedMedicine->id)
            ->get()
            ->map(function ($detail) use ($selectedMedicine) {
                return (object) [
                    'id' => $detail->id,
                    'date' => $detail->order->date ? \Carbon\Carbon::parse($detail->order->date)->format('d M Y') : 'N/A',
                    'req_no' => $detail->order->req_no ?? 'N/A',
                    'qty_requested' => $detail->qty_requested . ' ' . ($selectedMedicine->unit->unit_name ?? ''),
                    'requested_by' => $detail->order->requester->name ?? 'N/A',
                    'ms_approval' => $detail->order->ms_approval_status ?? 'Pending',
                    'qty_received' => $detail->qty_issued . ' ' . ($selectedMedicine->unit->unit_name ?? ''),
                    'issuing_officer' => $detail->order->approver->name ?? 'N/A',
                    'receiving_officer' => $detail->order->requester->name ?? 'N/A',
                ];
            });

        // Fetch Patient Administrations using Dispensation mapping
        $patientAdministrations = Dispensation::with(['admission.patient', 'issuedBy', 'batch'])
            ->whereHas('batch', function($q) use ($selectedMedicine) {
                $q->where('medicine_id', $selectedMedicine->id);
            })
            ->get()
            ->map(function ($admin) use ($selectedMedicine) {
                return (object) [
                    'id' => $admin->id,
                    'date' => $admin->date ? \Carbon\Carbon::parse($admin->date)->format('d M Y') : 'N/A',
                    'bht_no' => $admin->admission->bht_no ?? 'N/A',
                    'patient_name' => $admin->admission->patient->patient_name ?? 'N/A',
                    'qty_given' => $admin->qty_given . ' ' . ($selectedMedicine->unit->unit_name ?? ''),
                    'balance' => $selectedMedicine->stock . ' ' . ($selectedMedicine->unit->unit_name ?? ''),
                    'sister_initials' => $admin->issuedBy->name ?? 'N/A',
                    'remark' => trim(($admin->dosage ?? '') . ' ' . ($admin->usage_time ?? '')),
                ];
            });

        return view('medicines.medicine-dashboard', compact('category', 'medicines', 'selectedMedicine', 'pharmacyOrders', 'patientAdministrations'));
    }

    public function storeMedicine(Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'required|string|max:50|unique:medicines,item_code',
            'name' => 'required|string|max:150',
            'category_id' => 'required|integer|exists:categories,id',
            'unit_id' => 'required|integer|exists:units,id',
            'form_id' => 'required|integer|exists:medicine_forms,id',
            'strength' => 'nullable|string|max:50',
            'is_controlled' => 'boolean',
            'min_level' => 'required|integer|min:0',
            'warning_limit' => 'required|integer|min:0',
        ]);

        Medicine::create($validated);

        return redirect()->back()->with('success', 'Medicine added successfully.');
    }

    public function storeAdministration(Request $request)
    {
        $validated = $request->validate([
            'admission_id' => 'required|integer|exists:admissions,id',
            'batch_id' => 'required|integer|exists:medicine_batches,id',
            'qty_given' => 'required|integer|min:1',
            'dosage' => 'required|string|max:100',
            'usage_time' => 'nullable|string|max:100',
        ]);

        // Assuming authenticated user is issuing, fallback to 1 if no auth logic implemented yet
        $validated['issued_by'] = auth()->id() ?? 1;

        Dispensation::create($validated);

        return redirect()->back()->with('success', 'Administration recorded successfully.');
    }
}
