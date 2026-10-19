<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGeneralTransactionRequest;
use App\Models\GeneralItem;
use App\Models\GeneralTransaction;
use Illuminate\Http\RedirectResponse;

class GeneralTransactionController extends Controller
{
    /**
     * Store a newly created transaction for a general inventory item.
     */
    public function store(StoreGeneralTransactionRequest $request, GeneralItem $general_item): RedirectResponse
    {
        $validated = $request->validated();

        $quantityReceived = 0;
        $quantityIssued = 0;

        if ($validated['transaction_type'] === 'RECEIPT') {
            $quantityReceived = (int) $validated['quantity'];
        } else {
            $quantityIssued = (int) $validated['quantity'];
        }

        GeneralTransaction::create([
            'item_id' => $general_item->id,
            'ward_id' => $validated['ward_id'],
            'quantity_received' => $quantityReceived,
            'quantity_issued' => $quantityIssued,
            'date' => $validated['date'],
            'recorded_by' => $validated['recorded_by'],
        ]);

        $typeLabel = $validated['transaction_type'] === 'RECEIPT' ? 'Receipt' : 'Issue';

        return redirect()
            ->route('general-inventory.index', [
                'selected_item' => $general_item->id,
                'tab' => 'transactions',
            ])
            ->with('success', "{$typeLabel} of {$validated['quantity']} units recorded for {$general_item->name}.");
    }
}
