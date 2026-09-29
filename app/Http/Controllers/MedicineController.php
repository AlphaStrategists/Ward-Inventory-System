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

        $currentCategory = Category::where('name', $category)->first();
        $units = Unit::all();
        $medicineForms = MedicineForm::all();

        $admissions = collect([]);
        $availableBatches = collect([]);

        return view('medicines.medicine-dashboard', compact('category', 'medicines', 'selectedMedicine', 'pharmacyOrders', 'patientAdministrations', 'currentCategory', 'units', 'medicineForms', 'admissions', 'availableBatches'));
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

        // Backfill missing batches if stock > 0
        if ($selectedMedicine && $selectedMedicine->stock > 0) {
            $batchExists = \App\Models\MedicineBatch::where('medicine_id', $selectedMedicine->id)->exists();
            if (!$batchExists) {
                // Ensure a default ward exists
                $wardId = \Illuminate\Support\Facades\DB::table('wards')->value('id');
                if (!$wardId) {
                    $wardId = \Illuminate\Support\Facades\DB::table('wards')->insertGetId([
                        'ward_number' => 'W-01',
                        'ward_name' => 'Main Ward',
                    ]);
                }
                
                // Ensure a role exists
                $roleId = \Illuminate\Support\Facades\DB::table('roles')->value('id');
                if (!$roleId) {
                    $roleId = \Illuminate\Support\Facades\DB::table('roles')->insertGetId([
                        'role_name' => 'Admin',
                        'created_at' => now(),
                    ]);
                }

                // Ensure a user exists
                $userId = auth()->id() ?? \Illuminate\Support\Facades\DB::table('users')->value('id');
                if (!$userId) {
                    $userId = \Illuminate\Support\Facades\DB::table('users')->insertGetId([
                        'name' => 'System Admin',
                        'email' => 'admin@hospital.local',
                        'password' => bcrypt('password'),
                        'role_id' => $roleId,
                        'ward_id' => $wardId,
                        'created_at' => now(),
                    ]);
                }
                
                $batchId = \Illuminate\Support\Facades\DB::table('medicine_batches')->insertGetId([
                    'medicine_id' => $selectedMedicine->id,
                    'batch_no' => 'BATCH-INITIAL',
                    'expiry_date' => now()->addYear()->toDateString(),
                    'created_at' => now(),
                ]);

                \Illuminate\Support\Facades\DB::table('stock_receipts')->insert([
                    'batch_id' => $batchId,
                    'ward_id' => $wardId,
                    'quantity_received' => $selectedMedicine->stock,
                    'received_by' => $userId,
                    'date' => now(),
                ]);
            }
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

        $currentCategory = Category::where('name', $category)->first();
        $units = Unit::all();
        $medicineForms = MedicineForm::all();

        $admissions = \App\Models\Admission::with('patient')->orderBy('admit_date', 'desc')->get();

        $availableBatches = \App\Models\MedicineBatch::where('medicine_id', $selectedMedicine->id)
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($batch) {
                $received = \App\Models\StockReceipt::where('batch_id', $batch->id)->sum('quantity_received');
                $dispensed = \App\Models\Dispensation::where('batch_id', $batch->id)->sum('qty_given');
                $batch->available_qty = $received - $dispensed;
                return $batch;
            })
            ->filter(function ($batch) {
                return $batch->available_qty > 0;
            });

        return view('medicines.medicine-dashboard', compact('category', 'medicines', 'selectedMedicine', 'pharmacyOrders', 'patientAdministrations', 'currentCategory', 'units', 'medicineForms', 'admissions', 'availableBatches'));
    }

    public function storeMedicine($category, Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'required|string|max:50|unique:medicines,item_code',
            'name' => 'required|string|max:150',
            'category_id' => 'required|integer|exists:categories,id',
            'unit_id' => 'required|integer|exists:units,id',
            'form_id' => 'required|integer|exists:medicine_forms,id',
            'strength' => 'nullable|string|max:50',
            'min_level' => 'required|integer|min:0',
            'warning_limit' => 'required|integer|min:0',
            'initial_stock' => 'nullable|integer|min:0',
            'batch_no' => $request->input('initial_stock', 0) > 0 ? 'required|string|max:100' : 'nullable|string|max:100',
            'expiry_date' => $request->input('initial_stock', 0) > 0 ? 'required|date' : 'nullable|date',
        ]);

        // Security check: Force 'is_controlled' if category is narcotics
        if ($category === 'narcotics') {
            $validated['is_controlled'] = true;
        } else {
            $validated['is_controlled'] = $request->has('is_controlled');
        }

        // Enforce UPPERCASE formatting and trim spaces
        $validated['item_code'] = strtoupper(trim($validated['item_code']));
        $validated['name'] = strtoupper(trim($validated['name']));
        if (isset($validated['batch_no'])) {
            $validated['batch_no'] = strtoupper(trim($validated['batch_no']));
        }

        $initialStock = $validated['initial_stock'] ?? 0;
        $batchNo = $validated['batch_no'] ?? null;
        $expiryDate = $validated['expiry_date'] ?? null;
        
        unset($validated['initial_stock'], $validated['batch_no'], $validated['expiry_date']);

        $medicine = Medicine::create($validated);

        if ($initialStock > 0 || !empty($batchNo)) {
            // Ensure a default ward exists
            $wardId = \Illuminate\Support\Facades\DB::table('wards')->value('id');
            if (!$wardId) {
                $wardId = \Illuminate\Support\Facades\DB::table('wards')->insertGetId([
                    'ward_number' => 'W-01',
                    'ward_name' => 'Main Ward',
                ]);
            }

            // Ensure a role exists
            $roleId = \Illuminate\Support\Facades\DB::table('roles')->value('id');
            if (!$roleId) {
                $roleId = \Illuminate\Support\Facades\DB::table('roles')->insertGetId([
                    'role_name' => 'Admin',
                    'created_at' => now(),
                ]);
            }

            // Ensure a user exists
            $userId = auth()->id() ?? \Illuminate\Support\Facades\DB::table('users')->value('id');
            if (!$userId) {
                $userId = \Illuminate\Support\Facades\DB::table('users')->insertGetId([
                    'name' => 'System Admin',
                    'email' => 'admin@hospital.local',
                    'password' => bcrypt('password'),
                    'role_id' => $roleId,
                    'ward_id' => $wardId,
                    'created_at' => now(),
                ]);
            }

            // Create an initial batch
            $batchId = \Illuminate\Support\Facades\DB::table('medicine_batches')->insertGetId([
                'medicine_id' => $medicine->id,
                'batch_no' => $batchNo ?: 'INIT-BATCH',
                'expiry_date' => $expiryDate ?: now()->addYear()->toDateString(),
                'created_at' => now(),
            ]);

            // Add stock using stock_receipts which triggers the ledger
            \Illuminate\Support\Facades\DB::table('stock_receipts')->insert([
                'batch_id' => $batchId,
                'ward_id' => $wardId,
                'quantity_received' => $initialStock,
                'received_by' => $userId,
                'date' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Medicine added successfully.');
    }

    public function storeAdministration($category, $id, Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'admission_id' => 'required|integer|exists:admissions,id',
            'batch_id' => 'required|integer|exists:medicine_batches,id',
            'qty_given' => 'required|integer|min:1',
            'dosage' => 'required|string|max:100',
            'usage_time' => 'nullable|string|max:100',
        ]);

        $batch = \App\Models\MedicineBatch::findOrFail($validated['batch_id']);
        
        $received = \App\Models\StockReceipt::where('batch_id', $batch->id)->sum('quantity_received');
        $dispensed = \App\Models\Dispensation::where('batch_id', $batch->id)->sum('qty_given');
        $availableQty = $received - $dispensed;

        if ($validated['qty_given'] > $availableQty) {
            return redirect()->back()->with('error', 'Quantity given exceeds available batch quantity.');
        }

        $validated['issued_by'] = auth()->id() ?? 1;

        Dispensation::create($validated);

        // Deduct from main medicine stock
        $medicine = \App\Models\Medicine::findOrFail($id);
        $medicine->stock -= $validated['qty_given'];
        $medicine->save();

        return redirect()->back()->with('success', 'Administration recorded successfully.');
    }

    public function update(Request $request, $id)
    {
        abort_if(!auth()->check() || auth()->user()->role->role_name !== 'Admin', 403, 'Unauthorized action.');

        $validated = $request->validate([
            'item_code' => 'required|string|max:50|unique:medicines,item_code,'.$id,
            'name' => 'required|string|max:150',
            'form_id' => 'required|integer|exists:medicine_forms,id',
            'unit_id' => 'required|integer|exists:units,id',
            'strength' => 'nullable|string|max:50',
            'min_level' => 'required|integer|min:0',
            'warning_limit' => 'required|integer|min:0',
        ]);

        $medicine = Medicine::findOrFail($id);
        
        if ($medicine->category->name === 'narcotics') {
            $validated['is_controlled'] = true;
        } else {
            $validated['is_controlled'] = $request->has('is_controlled');
        }

        $validated['item_code'] = strtoupper($validated['item_code']);
        $validated['name'] = strtoupper($validated['name']);

        $medicine->update($validated);

        return redirect()->back()->with('success', 'Updated successfully');
    }

    public function destroy($id)
    {
        abort_if(!auth()->check() || auth()->user()->role->role_name !== 'Admin', 403, 'Unauthorized action.');

        $medicine = Medicine::findOrFail($id);
        
        try {
            $medicine->delete();
            return redirect()->back()->with('success', 'Deleted successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return redirect()->back()->with('error', 'Cannot delete this medicine because it contains existing batches or patient records. Please deactivate it instead.');
            }
            
            return redirect()->back()->with('error', 'An error occurred while deleting the medicine.');
        }
    }
}
