<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Medicine;
use App\Models\MedicineForm;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Unit;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Http\Request;

class InjectionController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ensure Category 'Injection' exists
        $category = Category::firstOrCreate(
            ['name' => 'Injection'],
            ['description' => 'Injection medicines for ward inventory']
        );

        // 2. Fetch medicines under Injection category with search filter
        $medicineQuery = Medicine::where('category_id', $category->id)->with(['unit', 'form']);
        
        if ($request->filled('search_medicine')) {
            $medicineQuery->where('name', 'like', '%' . $request->search_medicine . '%')
                          ->orWhere('item_code', 'like', '%' . $request->search_medicine . '%');
        }

        $medicines = $medicineQuery->get();

        // 3. Determine selected medicine (defaults to first medicine or requested medicine)
        $selectedMedicineId = $request->get('medicine_id', $medicines->first()?->id);
        $selectedMedicine = $medicines->firstWhere('id', $selectedMedicineId) ?? $medicines->first();

        // 4. Fetch orders / requisitions log for selected medicine or category
        $ordersQuery = OrderDetail::with(['order.requester', 'order.approver', 'order.ward', 'medicine.unit'])
            ->whereHas('medicine', function ($q) use ($category) {
                $q->where('category_id', $category->id);
            });

        if ($selectedMedicine) {
            $ordersQuery->where('medicine_id', $selectedMedicine->id);
        }

        if ($request->filled('search_records')) {
            $searchTerm = $request->search_records;
            $ordersQuery->whereHas('order', function ($q) use ($searchTerm) {
                $q->where('req_no', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('requester', function ($u) use ($searchTerm) {
                      $u->where('name', 'like', '%' . $searchTerm . '%');
                  });
            })->orWhereHas('medicine', function ($m) use ($searchTerm) {
                $m->where('name', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('date')) {
            $ordersQuery->whereHas('order', function ($q) use ($request) {
                $q->whereDate('date', $request->date);
            });
        }

        $requisitionLogs = $ordersQuery->orderBy('id', 'desc')->get();

        // 5. Lookups for dropdown forms
        $categories = Category::all();
        $units = Unit::all();
        $forms = MedicineForm::all();
        $wards = Ward::all();
        $users = User::all();

        return view('injections.index', compact(
            'category',
            'medicines',
            'selectedMedicine',
            'requisitionLogs',
            'categories',
            'units',
            'forms',
            'wards',
            'users'
        ));
    }
}
