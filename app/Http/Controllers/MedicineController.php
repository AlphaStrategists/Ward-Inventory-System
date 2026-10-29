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
use App\Models\StockReceipt;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MedicineController extends Controller
{
    /**
     * Resolve the active category model and fetch all medicines belonging to it,
     * ensuring newly added medicines without ward stock records or batches are not excluded.
     *
     * @param string $category
     * @return array{0: \App\Models\Category, 1: \Illuminate\Database\Eloquent\Collection<\App\Models\Medicine>}
     */
    protected function resolveCategoryAndMedicines(string $category): array
    {
        $categoryClean = strtolower(trim($category));
        $normalizedSlug = str_replace(['-', '_', ' '], '', $categoryClean);

        // Find all category records matching the active category name or common aliases/variations
        $matchingCategories = Category::where(function ($q) use ($category, $categoryClean, $normalizedSlug) {
            $q->where('name', $category)
              ->orWhereRaw('LOWER(name) = ?', [$categoryClean])
              ->orWhereRaw('LOWER(REPLACE(REPLACE(REPLACE(name, "-", ""), "_", ""), " ", "")) = ?', [$normalizedSlug]);

            if (str_contains($categoryClean, 'narcotic')) {
                $q->orWhereRaw('LOWER(name) LIKE ?', ['%narcotic%']);
            }

            if (str_contains($categoryClean, 'syrup')) {
                $q->orWhereRaw('LOWER(name) LIKE ?', ['%syrup%']);
            }

            if (str_contains($categoryClean, 'antibiotic')) {
                if (str_contains($categoryClean, 'oral')) {
                    $q->orWhereRaw('LOWER(name) LIKE ?', ['%oral%antibiotic%']);
                } else {
                    $q->orWhere(function ($sub) {
                        $sub->whereRaw('LOWER(name) LIKE ?', ['%antibiotic%'])
                            ->whereRaw('LOWER(name) NOT LIKE ?', ['%oral%']);
                    });
                }
            }

            if (str_contains($categoryClean, 'fluid') || str_contains($categoryClean, 'iv')) {
                $q->orWhereRaw('LOWER(name) LIKE ?', ['%fluid%'])
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%iv%']);
            }

            if (str_contains($categoryClean, 'countable')) {
                $q->orWhereRaw('LOWER(name) LIKE ?', ['%countable%']);
            }
        })->get();

        $currentCategory = $matchingCategories->firstWhere('name', $category)
            ?? $matchingCategories->firstWhere(fn ($cat) => strtolower($cat->name) === $categoryClean)
            ?? $matchingCategories->first()
            ?? Category::where('name', $category)->first()
            ?? Category::firstOrCreate(['name' => $category]);

        $categoryIds = $matchingCategories->pluck('id')->filter()->all();
        if ($currentCategory && !in_array($currentCategory->id, $categoryIds)) {
            $categoryIds[] = $currentCategory->id;
        }

        // Query medicines for this category without excluding medicines that do not have batches or stock_ledger records yet
        $medicinesQuery = Medicine::with(['unit', 'form'])
            ->where(function ($q) use ($categoryIds, $categoryClean) {
                if (!empty($categoryIds)) {
                    $q->whereIn('category_id', $categoryIds);
                }
                if (str_contains($categoryClean, 'narcotic')) {
                    $q->orWhere('is_controlled', true);
                }
            })
            ->orderBy('name');

        $medicines = $medicinesQuery->get();

        return [$currentCategory, $medicines];
    }

    public function index($category, Request $request)
    {
        [$currentCategory, $medicines] = $this->resolveCategoryAndMedicines($category);

        $firstMedicine = $medicines->first();

        if ($firstMedicine) {
            return redirect()->route('inventory.details', ['category' => $category, 'id' => $firstMedicine->id]);
        }

        $selectedMedicine = null;
        $pharmacyOrders = collect([]);
        $patientAdministrations = collect([]);
        $stockAdjustments = collect([]);
        $units = Unit::all();
        $medicineForms = MedicineForm::all();
        $suppliers = \App\Models\Supplier::orderBy('supplier_name')->get();

        $admissions = collect([]);
        $availableBatches = collect([]);
        return view('medicines.medicine-dashboard', compact('category', 'medicines', 'selectedMedicine', 'pharmacyOrders', 'patientAdministrations', 'stockAdjustments', 'currentCategory', 'units', 'medicineForms', 'suppliers', 'admissions', 'availableBatches'));
    }

    public function getDetails($category, $id)
    {
        [$currentCategory, $medicines] = $this->resolveCategoryAndMedicines($category);

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
                $userId = Auth::id() ?? \Illuminate\Support\Facades\DB::table('users')->value('id');
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

                $batch = MedicineBatch::create([
                    'medicine_id' => $selectedMedicine->id,
                    'batch_no' => 'BATCH-INITIAL',
                    'expiry_date' => now()->addYear()->toDateString(),
                    'created_at' => now(),
                ]);

                if ($selectedMedicine->stock > 0) {
                    StockReceipt::create([
                        'batch_id' => $batch->id,
                        'ward_id' => $wardId,
                        'quantity_received' => $selectedMedicine->stock,
                        'received_by' => $userId,
                        'date' => now(),
                    ]);
                }
            }
        }

        /** @var \App\Models\User $user */
        $wardId = Auth::user()->ward_id ?? DB::table('wards')->value('id');

        if ($wardId && $medicines->isNotEmpty()) {
            $stocks = DB::table('stock_ledger')
                ->join('medicine_batches', 'stock_ledger.batch_id', '=', 'medicine_batches.id')
                ->whereIn('medicine_batches.medicine_id', $medicines->pluck('id'))
                ->where('stock_ledger.ward_id', $wardId)
                ->groupBy('medicine_batches.medicine_id')
                ->select('medicine_batches.medicine_id', DB::raw('SUM(stock_ledger.quantity) as total_stock'))
                ->pluck('total_stock', 'medicine_id');

            foreach ($medicines as $medicine) {
                $medicine->stock = (int) ($stocks[$medicine->id] ?? 0);
            }
        } else {
            foreach ($medicines as $medicine) {
                $medicine->stock = 0;
            }
        }

        // Fetch Pharmacy Orders
        $selectedMedicine->load('batches.supplier');
        $pharmacyOrders = OrderDetail::with(['order.requester', 'order.approver', 'medicine.batches.supplier'])
            ->where('medicine_id', $selectedMedicine->id)
            ->get();

        // 1. Fetch all transactions (dispensations, receipts, adjustments) and map them for the ledger
        $dispensations = Dispensation::with(['admission.patient', 'issuedBy', 'batch'])
            ->whereHas('batch', function($q) use ($selectedMedicine) {
                $q->where('medicine_id', $selectedMedicine->id);
            })
            ->get()
            ->map(function ($item) {
                $dateOnly = $item->date ? \Carbon\Carbon::parse($item->date)->format('Y-m-d') : now()->format('Y-m-d');
                $time = $item->usage_time ?: '00:00:00';
                try {
                    $datetime = \Carbon\Carbon::parse($dateOnly . ' ' . $time);
                } catch (\Throwable $e) {
                    $datetime = \Carbon\Carbon::parse($dateOnly);
                }

                return (object) [
                    'type' => 'dispensation',
                    'timestamp' => $datetime->timestamp,
                    'id' => $item->id,
                    'model' => $item,
                    'qty' => -($item->qty_given) // Subtracted from balance
                ];
            });

        $receipts = \App\Models\StockReceipt::whereHas('batch', function($q) use ($selectedMedicine) {
                $q->where('medicine_id', $selectedMedicine->id);
            })
            ->get()
            ->map(function ($item) {
                return (object) [
                    'type' => 'receipt',
                    'timestamp' => \Carbon\Carbon::parse($item->date)->timestamp,
                    'id' => $item->id,
                    'model' => $item,
                    'qty' => $item->quantity_received // Added to balance
                ];
            });

        $adjustments = \App\Models\MedicineStockAdjustment::with(['batch', 'adjustedBy', 'ward'])
            ->whereHas('batch', function($q) use ($selectedMedicine) {
                $q->where('medicine_id', $selectedMedicine->id);
            })
            ->get()
            ->map(function ($item) {
                return (object) [
                    'type' => 'adjustment',
                    'timestamp' => \Carbon\Carbon::parse($item->created_at)->timestamp,
                    'id' => $item->id,
                    'model' => $item,
                    'qty' => $item->quantity // Can be positive or negative
                ];
            });

        // 2. Merge them into one collection and sort them ASCENDING by date/time and ID
        $ledger = $dispensations->concat($receipts)->concat($adjustments)->sortBy([
            ['timestamp', 'asc'],
            ['id', 'asc']
        ])->values();

        // 3 & 4 & 5. Loop FORWARD through the merged ledger
        $runningTotal = 0;
        foreach ($ledger as $entry) {
            $runningTotal += $entry->qty;
            $entry->model->dynamic_balance = $runningTotal;
        }

        // 6. Filter to keep only dispensations, and sort them DESCENDING (newest first)
        $patientAdministrations = $ledger->where('type', 'dispensation')
            ->sortBy([
                ['timestamp', 'desc'],
                ['id', 'desc']
            ])
            ->values()
            ->map(function ($entry) use ($selectedMedicine) {
                $admin = $entry->model;
                return (object) [
                    'id' => $admin->id,
                    'date' => $admin->date ? \Carbon\Carbon::parse($admin->date)->format('d M Y') : 'N/A',
                    'bht_no' => $admin->admission->bht_no ?? 'N/A',
                    'patient_name' => $admin->admission->patient->patient_name ?? 'N/A',
                    'qty_given' => $admin->qty_given . ' ' . ($selectedMedicine->unit->unit_name ?? ''),
                    'balance' => $admin->dynamic_balance . ' ' . ($selectedMedicine->unit->unit_name ?? ''),
                    'dynamic_balance' => $admin->dynamic_balance, // Raw number for the UI
                    'sister_initials' => $admin->issuedBy->name ?? 'N/A',
                    'remark' => $admin->dosage ?? '',
                    'usage_time' => $admin->usage_time,
                ];
            });

        // Fetch Stock Adjustments from ledger to maintain dynamic_balance
        $stockAdjustments = $ledger->where('type', 'adjustment')
            ->sortBy([
                ['timestamp', 'desc'],
                ['id', 'desc']
            ])
            ->values()
            ->map(function ($entry) {
                return $entry->model;
            });

        // $currentCategory is already resolved from resolveCategoryAndMedicines
        $units = Unit::all();
        $medicineForms = MedicineForm::all();
        $suppliers = \App\Models\Supplier::orderBy('supplier_name')->get();

        $admissions = \App\Models\Admission::with('patient')->orderBy('admit_date', 'desc')->get();

        $availableBatches = \App\Models\MedicineBatch::where('medicine_id', $selectedMedicine->id)
            ->where('expiry_date', '>=', now()->toDateString())
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

        return view('medicines.medicine-dashboard', compact('category', 'medicines', 'selectedMedicine', 'pharmacyOrders', 'patientAdministrations', 'stockAdjustments', 'currentCategory', 'units', 'medicineForms', 'suppliers', 'admissions', 'availableBatches'));
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
            'units_per_pack' => 'required|integer|min:1',
            'initial_stock' => 'nullable|integer|min:0',
            'stock_input_type' => 'nullable|string|in:unit,base',
            'batch_no' => $request->input('initial_stock', 0) > 0 ? 'required|string|max:100' : 'nullable|string|max:100',
            'expiry_date' => $request->input('initial_stock', 0) > 0 ? 'required|date' : 'nullable|date',
        ]);

        // Security check: Force 'is_controlled' if category is narcotics
        if (str_contains(strtolower($category), 'narcotic')) {
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

        DB::transaction(function () use ($validated, $initialStock, $batchNo, $expiryDate) {
            $medicine = Medicine::create($validated);

            if ($initialStock > 0 || !empty($batchNo)) {
                // Ensure a default ward exists
                $wardId = Auth::user()->ward_id ?? DB::table('wards')->value('id');
                if (!$wardId) {
                    $wardId = DB::table('wards')->insertGetId([
                        'ward_number' => 'W-01',
                        'ward_name' => 'Main Ward',
                    ]);
                }

                // Ensure a role exists
                $roleId = DB::table('roles')->value('id');
                if (!$roleId) {
                    $roleId = DB::table('roles')->insertGetId([
                        'role_name' => 'Admin',
                        'created_at' => now(),
                    ]);
                }

                // Ensure a user exists
                $userId = Auth::id() ?? DB::table('users')->value('id');
                if (!$userId) {
                    $userId = DB::table('users')->insertGetId([
                        'name' => 'System Admin',
                        'email' => 'admin@hospital.local',
                        'password' => bcrypt('password'),
                        'role_id' => $roleId,
                        'ward_id' => $wardId,
                        'created_at' => now(),
                    ]);
                }

                // Create an initial batch using Eloquent
                $batch = MedicineBatch::create([
                    'medicine_id' => $medicine->id,
                    'batch_no' => $batchNo ?: 'INIT-BATCH',
                    'expiry_date' => $expiryDate ?: now()->addYear()->toDateString(),
                    'created_at' => now(),
                ]);

                // Add stock using StockReceipt model which safely synchronizes with the ledger via Model Observer
                if ($initialStock > 0) {
                    StockReceipt::create([
                        'batch_id' => $batch->id,
                        'ward_id' => $wardId,
                        'quantity_received' => $initialStock,
                        'received_by' => $userId,
                        'date' => now(),
                    ]);
                }
            }
        });

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

            $wardId = Auth::user()->ward_id ?? \Illuminate\Support\Facades\DB::table('wards')->value('id');

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

        $validated['issued_by'] = Auth::id() ?? 1;

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

        $wardId = Auth::user()->ward_id ?? \Illuminate\Support\Facades\DB::table('wards')->value('id');
        $userId = Auth::id() ?? 1;

        \App\Models\MedicineStockAdjustment::create([
            'batch_id' => $validated['batch_id'],
            'ward_id' => $wardId,
            'adjustment_type' => $validated['adjustment_type'],
            'quantity' => $isDeduction ? -$validated['quantity'] : $validated['quantity'],
            'reason' => $validated['reason'],
            'adjusted_by' => $userId,
            'created_at' => \Carbon\Carbon::parse($validated['date'])->format('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Stock adjustment recorded successfully.');
    }

    public function update(Request $request, $id)
    {
        abort_if(!Auth::check() || (!Auth::user()->is_admin && Auth::user()->role !== 'Head Nurse'), 403, 'Unauthorized action.');

        $validated = $request->validate([
            'item_code' => 'required|string|max:50|unique:medicines,item_code,'.$id,
            'name' => 'required|string|max:150',
            'form_id' => 'required|integer|exists:medicine_forms,id',
            'unit_id' => 'required|integer|exists:units,id',
            'strength' => 'nullable|string|max:50',
            'min_level' => 'required|integer|min:0',
            'warning_limit' => 'required|integer|min:0',
            'units_per_pack' => 'required|integer|min:1',
        ]);

        $medicine = Medicine::findOrFail($id);

        if ($medicine->category && (str_contains(strtolower($medicine->category->name), 'narcotic') || $medicine->category->name === 'narcotics')) {
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
        abort_if(!Auth::check() || (!Auth::user()->is_admin && Auth::user()->role !== 'Head Nurse'), 403, 'Unauthorized action.');

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
            'units_per_pack' => 'required|integer|min:1',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
        ], [
            'req_no.unique' => 'This Requisition Number already exists. Please enter a new requisition number.'
        ]);

        $medicine = Medicine::findOrFail($id);

        $qtyRequested = $validated['qty_requested'] * $validated['units_per_pack'];

        $wardId = Auth::user()->ward_id ?? \Illuminate\Support\Facades\DB::table('wards')->value('id');
        $userId = Auth::id() ?? 1;

        $order = Order::create([
            'ward_id' => $wardId,
            'req_no' => $validated['req_no'],
            'date' => \Carbon\Carbon::parse($validated['date'])->format('Y-m-d H:i:s'),
            'requested_by' => $userId,
            'ms_approval_status' => 'Pending',
        ]);

        $formNameStr = strtolower($medicine->form->form_name ?? '');
        if (in_array($formNameStr, ['syrup', 'drops', 'suspension', 'solution', 'iv-fluid'])) {
            $singleUnitName = 'Bottle(s)';
        } elseif (in_array($formNameStr, ['injection'])) {
            $singleUnitName = 'Vial(s)';
        } elseif (in_array($formNameStr, ['capsule', 'tablet', 'pill'])) {
            $singleUnitName = ucfirst($formNameStr) . '(s)';
        } else {
            $singleUnitName = !empty($medicine->unit->unit_name) ? ucfirst($medicine->unit->unit_name) . '(s)' : 'Unit(s)';
        }

        $packLabel = $validated['units_per_pack'] > 1 ? 'Pack(s)' : $singleUnitName;
        $remark = $validated['qty_requested'] . ' ' . $packLabel;

        if (!empty($validated['supplier_id'])) {
            $supplierModel = Supplier::find($validated['supplier_id']);
            if ($supplierModel) {
                $remark = '[SUPPLIER:' . $supplierModel->supplier_name . '] ' . $remark;
            }
        }

        OrderDetail::create([
            'order_id' => $order->id,
            'medicine_id' => $medicine->id,
            'qty_requested' => $qtyRequested,
            'qty_issued' => 0,
            'remark' => $remark,
        ]);

        return redirect()->back()->with('success', 'Order created successfully.');
    }

    public function approveOrder(Request $request, $category, $id)
    {
        $detail = OrderDetail::findOrFail($id);
        $order = $detail->order;

        if (strtolower($order->ms_approval_status ?? '') === 'cancelled') {
            return redirect()->back()->with('error', 'Cannot approve a cancelled order.');
        }

        if ($request->input('action') === 'Approve') {
            $validated = $request->validate([
                'qty_requested' => 'required|integer|min:1',
            ]);

            $detail->update(['qty_requested' => $validated['qty_requested']]);
            $order->update([
                'ms_approval_status' => 'Approved',
                'approved_by' => Auth::id() ?? 1,
            ]);
            return redirect()->back()->with('success', 'Order approved successfully.');
        } else {
            $order->update([
                'ms_approval_status' => 'Rejected',
                'approved_by' => Auth::id() ?? 1,
            ]);
            return redirect()->back()->with('success', 'Order rejected successfully.');
        }
    }

    public function issueOrder(Request $request, $category, $id)
    {
        $detail = OrderDetail::findOrFail($id);

        if (strtolower($detail->order->ms_approval_status ?? '') === 'cancelled') {
            return redirect()->back()->with('error', 'Cannot issue a cancelled order.');
        }

        $validated = $request->validate([
            'qty_issued' => 'required|integer|min:1',
        ]);

        $qtyIssued = $validated['qty_issued'];

        if ($qtyIssued > $detail->qty_requested) {
            return redirect()->back()->with('error', 'Issued quantity cannot exceed requested quantity.');
        }

        $detail->update([
            'qty_issued' => $qtyIssued,
            'remark' => '[ISSUED_BY:' . \Illuminate\Support\Facades\Auth::user()->name . '] ' . ($detail->remark ?? '')
        ]);

        return redirect()->back()->with('success', 'Order issued successfully.');
    }

    public function cancelOrder(Request $request, $category, $id)
    {
        $detail = OrderDetail::find($id);
        $order = $detail ? $detail->order : Order::findOrFail($id);

        $currentUser = Auth::user();
        if (!$currentUser) {
            abort(401);
        }

        $isNurse = in_array($currentUser->role, ['Nurse', 'Head Nurse']) || $currentUser->hasRole(['Nurse', 'Head Nurse']) || in_array(strtolower($currentUser->role ?? ''), ['nurse', 'head nurse'], true);
        if (!$isNurse || (int) $order->requested_by !== (int) $currentUser->id) {
            return redirect()->back()->with('error', 'Only the nurse who created this order can cancel it.');
        }

        if (strtolower($order->ms_approval_status ?? '') !== 'pending') {
            return redirect()->back()->with('error', 'Only pending orders can be cancelled.');
        }

        $order->update([
            'ms_approval_status' => 'Cancelled',
        ]);

        return redirect()->back()->with('success', 'Order cancelled successfully.');
    }

    public function receiveOrder(Request $request, $category, $id)
    {
        $detail = OrderDetail::findOrFail($id);

        if (strtolower($detail->order->ms_approval_status ?? '') === 'cancelled') {
            return back()->with('error', 'Cannot receive a cancelled order.');
        }

        if (str_contains($detail->remark ?? '', '[RECEIVED')) {
            return back()->with('error', 'This order has already been received.');
        }

        $isNewBatch = $request->input('batch_type') === 'new';

        if ($isNewBatch) {
            $validated = $request->validate([
                'new_batch_no' => 'required|string|max:100',
                'new_expiry_date' => 'required|date',
                'supplier_id' => 'nullable|integer|exists:suppliers,id',
            ]);

            $batch = MedicineBatch::create([
                'medicine_id' => $detail->medicine_id,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'batch_no' => strtoupper(trim($validated['new_batch_no'])),
                'expiry_date' => $validated['new_expiry_date'],
                'created_at' => now(),
            ]);
            $batchId = $batch->id;
        } else {
            $validated = $request->validate([
                'batch_id' => 'required|integer|exists:medicine_batches,id',
                'supplier_id' => 'nullable|integer|exists:suppliers,id',
            ]);
            $batchId = $validated['batch_id'];

            if (!empty($validated['supplier_id'])) {
                MedicineBatch::where('id', $batchId)->update([
                    'supplier_id' => $validated['supplier_id'],
                ]);
            }
        }

        $wardId = Auth::user()->ward_id ?? DB::table('wards')->value('id');
        $userId = Auth::id() ?? 1;

        if ($detail->qty_issued > 0) {
            StockReceipt::create([
                'batch_id' => $batchId,
                'ward_id' => $wardId,
                'quantity_received' => $detail->qty_issued,
                'received_by' => $userId,
                'date' => now(),
            ]);
        }

        $remarkStr = $detail->remark ?? '';
        if (!empty($validated['supplier_id'])) {
            $supplierModel = Supplier::find($validated['supplier_id']);
            if ($supplierModel) {
                $remarkStr = preg_replace('/\[SUPPLIER:.*?\]\s*/', '', $remarkStr);
                $remarkStr = '[SUPPLIER:' . $supplierModel->supplier_name . '] ' . trim($remarkStr);
            }
        }

        $detail->update(['remark' => trim('[RECEIVED_BY:' . (Auth::user()->name ?? 'Nurse') . '] ' . $remarkStr)]);

        return redirect()->back()->with('success', 'Stock received successfully.');
    }

    /**
     * Process expired medicine batches, creating StockAdjustment deductions.
     */
    public function expireBatches()
    {
        \Illuminate\Support\Facades\Artisan::call('narcotics:expire-batches');

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Expired batches processed successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Expired batches processed successfully.');
    }
}
