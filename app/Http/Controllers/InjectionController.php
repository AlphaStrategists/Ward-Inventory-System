<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Medicine;
use App\Models\MedicineForm;
use App\Models\OrderDetail;
use App\Models\Unit;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Http\Request;

class InjectionController extends Controller
{
    /** Category shown by this page (overridden by sibling pages). */
    protected string $categoryName = 'Injection';

    protected string $categoryDescription = 'Injection medicines for ward inventory';

    protected string $view = 'injections.index';

    public function index(Request $request)
    {
        // 1. Ensure the category exists
        $category = Category::firstOrCreate(
            ['name' => $this->categoryName],
            ['description' => $this->categoryDescription]
        );

        // 2. Fetch medicines under this category with optional search filter
        $medicines = Medicine::where('category_id', $category->id)
            ->with(['unit', 'form'])
            ->when($request->filled('search_medicine'), function ($query) use ($request) {
                $term = $request->search_medicine;
                // Grouped so the OR cannot escape the category filter
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', '%' . $term . '%')
                      ->orWhere('item_code', 'like', '%' . $term . '%');
                });
            })
            ->orderBy('name')
            ->get();

        // 3. Selected medicine: requested one, otherwise the first in the list
        $selectedMedicine = $request->filled('medicine_id')
            ? ($medicines->firstWhere('id', (int) $request->medicine_id) ?? $medicines->first())
            : $medicines->first();

        // 4. Requisition log for the selected medicine (or the whole category)
        $ordersQuery = OrderDetail::with(['order.requester', 'order.approver', 'order.ward', 'medicine.unit'])
            ->whereHas('medicine', function ($q) use ($category) {
                $q->where('category_id', $category->id);
            });

        if ($selectedMedicine) {
            $ordersQuery->where('medicine_id', $selectedMedicine->id);
        }

        if ($request->filled('search_records')) {
            $searchTerm = $request->search_records;
            // Grouped so the OR cannot escape the category / medicine filters
            $ordersQuery->where(function ($query) use ($searchTerm) {
                $query->whereHas('order', function ($q) use ($searchTerm) {
                    $q->where('req_no', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('requester', function ($u) use ($searchTerm) {
                          $u->where('name', 'like', '%' . $searchTerm . '%');
                      });
                })->orWhereHas('medicine', function ($m) use ($searchTerm) {
                    $m->where('name', 'like', '%' . $searchTerm . '%');
                });
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
        $users = User::with('role')->get();

        return view($this->view, compact(
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
