<?php

namespace App\Http\Controllers;

use App\Models\GeneralInventory;
use Illuminate\Http\Request;

class GeneralInventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = GeneralInventory::query();

        if ($request->filled('search')) {
            $query->where('item_name', 'like', '%' . $request->search . '%')
                ->orWhere('item_code', 'like', '%' . $request->search . '%');
        }

        $inventories = $query->orderBy('inventory_id', 'desc')->get();

        return view('general-inventory.index', compact('inventories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:100',
            'item_code' => 'nullable|string|max:30',
            'entry_date' => 'required|date',
            'received' => 'required|integer|min:0',
            'issued' => 'required|integer|min:0',
        ]);

        $validated['balance'] = max(0, $validated['received'] - $validated['issued']);

        GeneralInventory::create($validated);

        return redirect()->back()->with('success', 'General inventory item logged successfully.');
    }

    public function update(Request $request, $id)
    {
        $inventory = GeneralInventory::findOrFail($id);

        $validated = $request->validate([
            'item_name' => 'required|string|max:100',
            'item_code' => 'nullable|string|max:30',
            'entry_date' => 'required|date',
            'received' => 'required|integer|min:0',
            'issued' => 'required|integer|min:0',
        ]);

        $validated['balance'] = max(0, $validated['received'] - $validated['issued']);

        $inventory->update($validated);

        return redirect()->back()->with('success', 'General inventory updated successfully.');
    }
}
