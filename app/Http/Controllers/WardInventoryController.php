<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicine;
use App\Models\Category;
use App\Models\Unit;
use App\Models\MedicineForm;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\MedicineBatch;
use App\Models\StockReceipt;
use App\Models\Ward;
use App\Models\User;

class WardInventoryController extends Controller
{
    /**
     * Display the Injection Ward Inventory page interface.
     */
    public function injection(Request $request)
    {
        // 1. Search filter for sidebar medicines
        $medicineQuery = Medicine::with(['unit', 'category', 'form']);
        
        if ($request->filled('search_medicine')) {
            $search = $request->query('search_medicine');
            $medicineQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        $medicines = $medicineQuery->get();

        // 2. Determine selected medicine (query param or first item)
        $selectedId = $request->query('medicine_id');
        $selectedMedicine = $selectedId 
            ? $medicines->firstWhere('id', $selectedId) 
            : $medicines->first();

        // 3. Query orders list for requisitions log with optional search and date filtering
        $orderQuery = Order::with(['requester', 'approver', 'details.medicine.unit'])
            ->orderBy('date', 'desc');

        if ($request->filled('search_records')) {
            $searchRec = $request->query('search_records');
            $orderQuery->where(function($q) use ($searchRec) {
                $q->where('req_no', 'like', "%{$searchRec}%")
                  ->orWhereHas('details.medicine', function($m) use ($searchRec) {
                      $m->where('name', 'like', "%{$searchRec}%");
                  });
            });
        }

        if ($request->filled('date_filter')) {
            try {
                $formattedDate = \Carbon\Carbon::parse($request->query('date_filter'))->format('Y-m-d');
                $orderQuery->whereDate('date', $formattedDate);
            } catch (\Exception $e) {
                // Ignore invalid date strings
            }
        }

        $orders = $orderQuery->get();

        // 4. Lookups for dropdowns
        $categories = Category::all();
        $units = Unit::all();
        $forms = MedicineForm::all();

        return view('ward-inventory.injection', compact(
            'medicines',
            'selectedMedicine',
            'orders',
            'categories',
            'units',
            'forms'
        ));
    }

    /**
     * Store a newly created medicine into the database.
     */
    public function storeMedicine(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'item_code' => 'required|string|max:50|unique:medicines,item_code',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'form_id' => 'required|exists:medicine_forms,id',
            'strength' => 'nullable|string|max:50',
            'initial_stock' => 'required|integer|min:0',
            'batch_no' => 'required|string|max:100',
            'expiry_date' => 'required|date',
        ]);

        // Create Medicine
        $medicine = Medicine::create([
            'name' => $validated['name'],
            'item_code' => $validated['item_code'],
            'category_id' => $validated['category_id'],
            'unit_id' => $validated['unit_id'],
            'form_id' => $validated['form_id'],
            'strength' => $validated['strength'],
        ]);

        // Create Default Ward & User if not present
        $ward = Ward::firstOrCreate(['ward_number' => '48'], ['ward_name' => 'Ward 48']);
        $user = User::first() ?? User::create([
            'name' => 'Ward Staff',
            'email' => 'staff@ward48.local',
            'password' => bcrypt('password'),
            'role_id' => 1,
            'ward_id' => $ward->id,
        ]);

        // Create Batch
        $batch = MedicineBatch::create([
            'medicine_id' => $medicine->id,
            'batch_no' => $validated['batch_no'],
            'expiry_date' => $validated['expiry_date'],
        ]);

        // Create Initial Stock Receipt if > 0
        if ($validated['initial_stock'] > 0) {
            StockReceipt::create([
                'batch_id' => $batch->id,
                'ward_id' => $ward->id,
                'quantity_received' => $validated['initial_stock'],
                'received_by' => $user->id,
                'date' => now(),
            ]);
        }

        return redirect()->route('ward-inventory.injection', ['medicine_id' => $medicine->id])
            ->with('success', 'Medicine added successfully!');
    }

    /**
     * Store a newly created pharmacy requisition order.
     */
    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'qty_requested' => 'required|integer|min:1',
            'remark' => 'nullable|string',
        ]);

        $ward = Ward::firstOrCreate(['ward_number' => '48'], ['ward_name' => 'Ward 48']);
        $user = User::first() ?? User::create([
            'name' => 'Ward Staff',
            'email' => 'staff@ward48.local',
            'password' => bcrypt('password'),
            'role_id' => 1,
            'ward_id' => $ward->id,
        ]);

        $reqNo = 'REQ-' . date('Y') . '-' . str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT);

        $order = Order::create([
            'ward_id' => $ward->id,
            'req_no' => $reqNo,
            'date' => now(),
            'requested_by' => $user->id,
            'ms_approval_status' => 'Pending',
            'remark' => $validated['remark'] ?? null,
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'medicine_id' => $validated['medicine_id'],
            'qty_requested' => $validated['qty_requested'],
            'qty_issued' => 0,
        ]);

        return redirect()->route('ward-inventory.injection', ['medicine_id' => $validated['medicine_id']])
            ->with('success', 'Pharmacy Requisition Order added successfully!');
    }
}
