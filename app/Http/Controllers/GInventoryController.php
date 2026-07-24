<?php

namespace App\Http\Controllers;

use App\Models\GenInventory;
use Illuminate\Http\Request;

class GInventoryController extends Controller
{
    public function view() {
        return view('pages/genInventory');
    }

    public function index()
    {
        $items = GenInventory::orderBy('date', 'desc')->get();

        $lowStockCount = GenInventory::all()
            ->filter(fn($item) => $item->balance <= 3)
            ->count();

        return view('Pages.GenInventory', [
            'items' => $items,
            'lowStockCount' => $lowStockCount,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item' => 'required|string|max:255',
            'recceived date' => 'required|date',
            'code' => 'required|string|unique:gen_inventory,code',
            'received' => 'required|integer|min:0',
        ]);

        $validated['issued'] = 0;

        $item = GenInventory::create($validated);

        return response()->json($item);
    }

    public function update(Request $request, GenInventory $inventoryItem)
    {
        $validated = $request->validate([
            'item' => 'sometimes|string|max:255',
            'received' => 'sometimes|integer|min:0',
            'issued' => 'sometimes|integer|min:0',
        ]);

        $inventoryItem->update($validated);

        return response()->json($inventoryItem);
    }

    public function destroy(GenInventory $inventoryItem)
    {
        $inventoryItem->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}