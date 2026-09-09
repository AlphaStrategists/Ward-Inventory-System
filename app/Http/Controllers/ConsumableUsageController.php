<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ConsumableUsage;
use App\Models\Staff;
use Illuminate\Http\Request;

class ConsumableUsageController extends Controller
{
    public function index(Request $request)
    {
        $query = ConsumableUsage::with(['item', 'inchargeStaff']);

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        $usages = $query->orderBy('usage_id', 'desc')->get();
        $consumableItems = Item::where('workflow_pattern', 'consumable_direct')->get();
        $staffList = Staff::orderBy('name')->get();

        return view('consumable-usage.index', compact('usages', 'consumableItems', 'staffList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,item_id',
            'bed_head_no' => 'nullable|string|max:20',
            'usage_date' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'incharge_staff_id' => 'required|exists:staff,staff_id',
            'notes' => 'nullable|string',
        ]);

        $item = Item::findOrFail($validated['item_id']);

        if ($item->workflow_pattern !== 'consumable_direct') {
            return redirect()->back()->withErrors(['item_id' => 'Selected item is not a direct consumable item.']);
        }

        $newBalance = max(0, $item->quantity - $validated['quantity']);
        $item->update(['quantity' => $newBalance]);

        ConsumableUsage::create([
            'item_id' => $item->item_id,
            'bed_head_no' => $validated['bed_head_no'] ?? null,
            'usage_date' => $validated['usage_date'],
            'quantity' => $validated['quantity'],
            'balance' => $newBalance,
            'incharge_staff_id' => $validated['incharge_staff_id'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Consumable usage logged successfully.');
    }
}
