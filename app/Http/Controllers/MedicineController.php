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
        $currentCategory = Category::where('name', $category)->firstOrFail();

        $medicines = Medicine::with(['unit', 'form'])->where('category_id', $currentCategory->id)->get();
        
        $firstMedicine = $medicines->first();

        if ($firstMedicine) {
            return redirect()->route('inventory.details', ['category' => $category, 'id' => $firstMedicine->id]);
        }

        $selectedMedicine = null;
        $pharmacyOrders = collect([]);
        $patientAdministrations = collect([]);
        $units = Unit::all();
        $medicineForms = MedicineForm::all();

        $admissions = collect([]);
        $availableBatches = collect([]);
        return view('medicines.medicine-dashboard', compact('category', 'medicines', 'selectedMedicine', 'pharmacyOrders', 'patientAdministrations', 'currentCategory', 'units', 'medicineForms', 'admissions', 'availableBatches'));
    }

    public function getDetails($category, $id)
    {
        $currentCategory = Category::where('name', $category)->firstOrFail();

        $medicines = Medicine::with(['unit', 'form'])->where('category_id', $currentCategory->id)->get();
        
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

        $wardId = auth()->user()->ward_id ?? \Illuminate\Support\Facades\DB::table('wards')->value('id');

        foreach ($medicines as $medicine) {
            $medicine->stock = \Illuminate\Support\Facades\DB::table('stock_ledger')
                ->join('medicine_batches', 'stock_ledger.batch_id', '=', 'medicine_batches.id')
                ->where('medicine_batches.medicine_id', $medicine->id)
                ->where('stock_ledger.ward_id', $wardId)
                ->sum('stock_ledger.quantity');
        }

        // Fetch Pharmacy Orders
        $pharmacyOrders = OrderDetail::with(['order.requester', 'order.approver'])
            ->where('medicine_id', $selectedMedicine->id)
            ->get();

        // Fetch Patient Administrations using Dispensation mapping
        $patientAdministrations = Dispensation::with(['admission.patient', 'issuedBy', 'batch'])
            ->whereHas('batch', function($q) use ($selectedMedicine) {
                $q->where('medicine_id', $selectedMedicine->id);
            })
            ->orderBy('date', 'desc')
            ->orderBy('usage_time', 'desc')
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
                    'remark' => $admin->dosage ?? '',
                    'usage_time' => $admin->usage_time,
                ];
            });

        // Fetch Stock Adjustments
        $stockAdjustments = \App\Models\MedicineStockAdjustment::with(['batch', 'adjustedBy', 'ward'])
            ->whereHas('batch', function($q) use ($selectedMedicine) {
                $q->where('medicine_id', $selectedMedicine->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

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
                $adjustments = \App\Models\MedicineStockAdjustment::where('batch_id', $batch->id)->sum('quantity');
                $batch->available_qty = $received - $dispensed + $adjustments;
                return $batch;
            })
            ->filter(function ($batch) {
                return $batch->available_qty > 0;
            });

        return view('medicines.medicine-dashboard', compact('category', 'medicines', 'selectedMedicine', 'pharmacyOrders', 'patientAdministrations', 'stockAdjustments', 'currentCategory', 'units', 'medicineForms', 'admissions', 'availableBatches'));
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
            'stock_input_type' => 'nullable|string|in:unit,base',
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

        $initialStock = (int) ($validated['initial_stock'] ?? 0);
        $batchNo = $validated['batch_no'] ?? null;
        $expiryDate = $validated['expiry_date'] ?? null;
        $stockInputType = $validated['stock_input_type'] ?? 'unit';
        $form = \App\Models\MedicineForm::find($validated['form_id']);
        $isLiquid = $form && in_array(strtolower($form->form_name), ['syrup', 'drops', 'injection', 'iv-fluid']);

        if ($isLiquid && $stockInputType === 'unit') {
            preg_match('/(\d+)/', $validated['strength'] ?? '', $matches);
            $baseVolume = (int) ($matches[1] ?? 1);
            if ($baseVolume > 0) {
                $initialStock = $initialStock * $baseVolume;
            }
        }
        
        unset($validated['initial_stock'], $validated['batch_no'], $validated['expiry_date'], $validated['stock_input_type']);

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

    public function storeAdministration(Request $request, $category, $id)
    {
        $medicine = Medicine::with('category')->findOrFail($id);
        $isBulk = strtolower($medicine->category->name ?? '') === 'bulk';

        $validated = $request->validate([
            'date' => 'required|date',
            'admission_id' => $isBulk ? 'nullable' : 'required|integer|exists:admissions,id',
            'batch_id' => 'required|integer|exists:medicine_batches,id',
            'qty_given' => 'required|integer|min:1',
            'dosage' => 'required|string|max:100',
            'usage_time' => 'nullable|string|max:100',
        ]);

        if ($isBulk) {
            $dummyPatient = \App\Models\Patient::firstOrCreate(
                ['patient_name' => 'Ward General Use']
            );

            $wardId = auth()->user()->ward_id ?? \Illuminate\Support\Facades\DB::table('wards')->value('id');

            $dummyAdmission = \App\Models\Admission::firstOrCreate(
                ['bht_no' => 'BULK-GENERAL'],
                [
                    'patient_id' => $dummyPatient->id,
                    'ward_id' => $wardId,
                    'status' => 'Admitted'
                ]
            );
            $validated['admission_id'] = $dummyAdmission->id;
        }

        $batch = \App\Models\MedicineBatch::findOrFail($validated['batch_id']);
        
        $received = \App\Models\StockReceipt::where('batch_id', $batch->id)->sum('quantity_received');
        $dispensed = \App\Models\Dispensation::where('batch_id', $batch->id)->sum('qty_given');
        $availableQty = $received - $dispensed;

        if ($validated['qty_given'] > $availableQty) {
            return redirect()->back()->with('error', 'Quantity given exceeds available batch quantity.');
        }

        $validated['issued_by'] = auth()->id() ?? 1;

        // Ensure usage_time is saved exactly as requested
        $validated['usage_time'] = $request->usage_time;

        Dispensation::create($validated);


        return redirect()->back()->with('success', 'Administration recorded successfully.');
    }

    public function storeAdjustment(Request $request, $category, $id)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'batch_id' => 'required|integer|exists:medicine_batches,id',
            'adjustment_type' => 'required|in:EXPIRY,DAMAGED,LOST,COUNT_CORRECTION,RETURN',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        $batch = \App\Models\MedicineBatch::findOrFail($validated['batch_id']);
        
        $received = \App\Models\StockReceipt::where('batch_id', $batch->id)->sum('quantity_received');
        $dispensed = \App\Models\Dispensation::where('batch_id', $batch->id)->sum('qty_given');
        $adjustments = \App\Models\MedicineStockAdjustment::where('batch_id', $batch->id)->sum('quantity');
        $availableQty = $received - $dispensed + $adjustments;

        $isDeduction = in_array($validated['adjustment_type'], ['EXPIRY', 'DAMAGED', 'LOST']);

        if ($isDeduction && $validated['quantity'] > $availableQty) {
            return redirect()->back()->with('error', 'Adjustment quantity exceeds available batch quantity.');
        }

        $wardId = auth()->user()->ward_id ?? \Illuminate\Support\Facades\DB::table('wards')->value('id');
        $userId = auth()->id() ?? 1;

        \App\Models\MedicineStockAdjustment::create([
            'batch_id' => $validated['batch_id'],
            'ward_id' => $wardId,
            'adjustment_type' => $validated['adjustment_type'],
            'quantity' => $isDeduction ? -$validated['quantity'] : $validated['quantity'],
            'reason' => $validated['reason'],
            'adjusted_by' => $userId,
            'created_at' => $validated['date'] . ' ' . date('H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Stock adjustment recorded successfully.');
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

    public function storeOrder(Request $request, $category, $id)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'req_no' => 'required|string|max:100|unique:orders,req_no',
            'qty_requested' => 'required|integer|min:1',
        ], [
            'req_no.unique' => 'This Requisition Number already exists. Please enter a new requisition number.'
        ]);

        $medicine = Medicine::findOrFail($id);
        $wardId = auth()->user()->ward_id ?? \Illuminate\Support\Facades\DB::table('wards')->value('id');
        $userId = auth()->id() ?? 1;

        $order = Order::create([
            'ward_id' => $wardId,
            'req_no' => $validated['req_no'],
            'date' => $validated['date'],
            'requested_by' => $userId,
            'ms_approval_status' => 'Pending',
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'medicine_id' => $medicine->id,
            'qty_requested' => $validated['qty_requested'],
            'qty_issued' => 0,
        ]);

        return redirect()->back()->with('success', 'Order created successfully.');
    }

    public function approveOrder(Request $request, $category, $id)
    {
        $detail = OrderDetail::findOrFail($id);
        $order = $detail->order;

        if ($request->input('action') === 'Approve') {
            $validated = $request->validate([
                'qty_requested' => 'required|integer|min:1',
            ]);
            
            $detail->update(['qty_requested' => $validated['qty_requested']]);
            $order->update([
                'ms_approval_status' => 'Approved',
                'approved_by' => auth()->id() ?? 1,
            ]);
            return redirect()->back()->with('success', 'Order approved successfully.');
        } else {
            $order->update([
                'ms_approval_status' => 'Rejected',
                'approved_by' => auth()->id() ?? 1,
            ]);
            return redirect()->back()->with('success', 'Order rejected successfully.');
        }
    }

    public function issueOrder(Request $request, $category, $id)
    {
        $detail = OrderDetail::findOrFail($id);
        
        $validated = $request->validate([
            'qty_issued' => 'required|integer|min:1|max:' . $detail->qty_requested,
        ]);

        $detail->update([
            'qty_issued' => $validated['qty_issued'],
            'remark' => '[ISSUED_BY:' . \Illuminate\Support\Facades\Auth::user()->name . '] ' . ($detail->remark ?? '')
        ]);
        
        return redirect()->back()->with('success', 'Order issued successfully.');
    }

    public function receiveOrder(Request $request, $category, $id)
    {
        $detail = OrderDetail::findOrFail($id);

        if (str_contains($detail->remark ?? '', '[RECEIVED')) {
            return back()->with('error', 'This order has already been received.');
        }
        
        $isNewBatch = $request->input('batch_type') === 'new';

        if ($isNewBatch) {
            $validated = $request->validate([
                'new_batch_no' => 'required|string|max:100',
                'new_expiry_date' => 'required|date',
            ]);
            
            $batchId = \Illuminate\Support\Facades\DB::table('medicine_batches')->insertGetId([
                'medicine_id' => $detail->medicine_id,
                'batch_no' => strtoupper(trim($validated['new_batch_no'])),
                'expiry_date' => $validated['new_expiry_date'],
                'created_at' => now(),
            ]);
        } else {
            $validated = $request->validate([
                'batch_id' => 'required|integer|exists:medicine_batches,id',
            ]);
            $batchId = $validated['batch_id'];
        }

        $wardId = auth()->user()->ward_id ?? \Illuminate\Support\Facades\DB::table('wards')->value('id');
        $userId = auth()->id() ?? 1;

        \Illuminate\Support\Facades\DB::table('stock_receipts')->insert([
            'batch_id' => $batchId,
            'ward_id' => $wardId,
            'quantity_received' => $detail->qty_issued,
            'received_by' => $userId,
            'date' => now(),
        ]);

        $detail->update(['remark' => trim('[RECEIVED_BY:' . (auth()->user()->name ?? 'Nurse') . '] ' . ($detail->remark ?? ''))]);

        return redirect()->back()->with('success', 'Stock received successfully.');
    }
}
