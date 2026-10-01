<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    /**
     * Store a newly created medicine item in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'required|string|max:50|unique:medicines,item_code',
            'name' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'form_id' => 'required|exists:medicine_forms,id',
            'strength' => 'nullable|string|max:50',
            'min_level' => 'required|integer|min:1',
            'warning_limit' => 'required|integer|min:1',
        ]);

        $medicine = Medicine::create($validated);

        return redirect()->back()->with('success', 'Medicine "' . $medicine->name . '" added successfully to database!');
    }

    /**
     * Delete a medicine item from database.
     */
    public function destroy(Medicine $medicine)
    {
        $medicineName = $medicine->name;
        $medicine->delete();

        return redirect()->back()->with('success', 'Medicine "' . $medicineName . '" deleted successfully.');
    }
}
