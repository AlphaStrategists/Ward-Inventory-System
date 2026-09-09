<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Staff;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query();

        if ($request->filled('type')) {
            $query->where('item_type', $request->type);
        }

        if ($request->filled('subtype')) {
            $query->where('item_subtype', $request->subtype);
        }

        if ($request->filled('search')) {
            $query->where('item_name', 'like', '%' . $request->search . '%');
        }

        $items = $query->orderBy('item_name')->get();
        $selectedItem = null;

        if ($request->filled('selected_id')) {
            $selectedItem = Item::find($request->selected_id);
        } elseif ($items->isNotEmpty()) {
            $selectedItem = $items->first();
        }

        $activeTransactions = collect();
        if ($selectedItem) {
            $selectedItem->load([
                'requestedStocks.requestedBy',
                'requestedStocks.approvedByMs',
                'requestedStocks.issuedOfficer',
                'requestedStocks.confirmReceivedBy',
                'narcoticUsages.patient',
                'narcoticUsages.recordedBy',
                'consumableUsages.inchargeStaff',
            ]);
            $activeTransactions = $selectedItem->active_transactions;
        }

        $staffList = Staff::orderBy('name')->get();

        return view('items.index', compact('items', 'selectedItem', 'activeTransactions', 'staffList'));
    }

    public function show($id)
    {
        $item = Item::with([
            'requestedStocks.requestedBy',
            'requestedStocks.confirmRequestBy',
            'requestedStocks.approvedByMs',
            'requestedStocks.issuedOfficer',
            'requestedStocks.confirmReceivedBy',
            'narcoticUsages.patient',
            'narcoticUsages.recordedBy',
            'consumableUsages.inchargeStaff',
        ])->findOrFail($id);

        return view('items.show', compact('item'));
    }

    public function create()
    {
        return view('items.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:100',
            'item_type' => 'required|in:medicine,surgical,injection',
            'item_subtype' => 'required|in:narcotic,syrup,iv_fluid,oral_countable,oral_antibiotic,bulk_medicine,surgical_consumable_1,surgical_consumable_2,local_purchase,injection,injection_antibiotic',
            'workflow_pattern' => 'required|in:request_simple,request_approved,narcotic_direct,consumable_direct',
            'quantity' => 'required|integer|min:0',
        ]);

        $item = Item::create($validated);

        return redirect()->route('items.index', ['selected_id' => $item->item_id])
            ->with('success', 'Item created successfully.');
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $validated = $request->validate([
            'item_name' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
        ]);

        $item->update($validated);

        return redirect()->route('items.index', ['selected_id' => $item->item_id])
            ->with('success', 'Item updated successfully.');
    }
}
