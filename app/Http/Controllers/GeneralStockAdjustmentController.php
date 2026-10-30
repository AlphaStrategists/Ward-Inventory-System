<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGeneralStockAdjustmentRequest;
use App\Models\GeneralItem;
use App\Models\GeneralStockAdjustment;
use Illuminate\Http\RedirectResponse;

class GeneralStockAdjustmentController extends Controller
{
    /**
     * Store a stock adjustment (DAMAGED, LOST, COUNT_CORRECTION, RETURN) for a general item.
     */
    public function store(StoreGeneralStockAdjustmentRequest $request, GeneralItem $general_item): RedirectResponse
    {
        $validated = $request->validated();

        $qty = (int) $validated['quantity'];
        // In general stock adjustments, DAMAGED and LOST naturally decrease stock unless explicitly signed
        if (in_array($validated['adjustment_type'], ['DAMAGED', 'LOST']) && $qty > 0) {
            $qty = -$qty;
        }

        GeneralStockAdjustment::create([
            'item_id' => $general_item->id,
            'ward_id' => $validated['ward_id'],
            'adjustment_type' => $validated['adjustment_type'],
            'quantity' => $qty,
            'reason' => $validated['reason'],
            'adjusted_by' => $validated['adjusted_by'],
            'created_at' => now(),
        ]);

        return redirect()
            ->route('general-inventory.index', [
                'selected_item' => $general_item->id,
                'tab' => 'adjustments',
            ])
            ->with('success', "Stock adjustment ({$validated['adjustment_type']}) of {$validated['quantity']} recorded.");
    }
}
