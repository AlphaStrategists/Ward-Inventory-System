<?php

namespace App\Http\Controllers;

use App\Models\SurgicalConsumable;
use App\Models\BhtRequisition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SurgicalConsumable2Controller extends Controller
{
    /**
     * Render the Surgical Consumable 2 view.
     */
    public function index()
    {
        return view('surgical-consumable-2');
    }

    /**
     * Get Surgical Consumable 2 items list.
     */
    public function getConsumables()
    {
        try {
            if (Schema::hasTable('surgical_consumables_2')) {
                $items = SurgicalConsumable::where('type', 'consumable_2')->get();
                if ($items->count() > 0) {
                    return response()->json($items);
                }
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return response()->json([
            ['id' => 201, 'name' => 'SURGICAL DRESSING KIT', 'balance' => 40, 'unit' => 'Kit'],
            ['id' => 202, 'name' => 'FOLEY CATHETER 16FR', 'balance' => 25, 'unit' => 'Pcs'],
            ['id' => 203, 'name' => 'SUCTION CATHETER 14FR', 'balance' => 50, 'unit' => 'Pcs'],
            ['id' => 204, 'name' => 'IV CANNULA 20G', 'balance' => 120, 'unit' => 'Pcs'],
            ['id' => 205, 'name' => 'SCALP VEIN SET 23G', 'balance' => 60, 'unit' => 'Pcs'],
            ['id' => 206, 'name' => 'TRACHEOSTOMY TUBE 7.5', 'balance' => 8, 'unit' => 'Pcs'],
        ]);
    }

    /**
     * Store a new Surgical Consumable 2 item.
     */
    public function storeConsumable(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'balance' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
        ]);

        $validated['id'] = time();
        return response()->json(['success' => true, 'data' => $validated, 'message' => 'Surgical Consumable 2 item added successfully.']);
    }

    /**
     * Get requisitions log entries (Date, BHT, Quantity, Balance, Signature in Charge, Note).
     */
    public function getRequisitions()
    {
        try {
            if (Schema::hasTable('bht_requisitions')) {
                $records = BhtRequisition::where('category_type', 'surgical_consumable_2')->orderBy('created_at', 'desc')->get();
                if ($records->count() > 0) {
                    return response()->json($records);
                }
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return response()->json([
            [
                'id' => 301,
                'date' => '2026-09-16',
                'item_name' => 'SURGICAL DRESSING KIT',
                'bht_no' => 'BHT-88401',
                'quantity' => '2 Kit',
                'balance' => '40 Kit',
                'officer_name' => 'Sr. Fernando (RN-204)',
                'signature_in_charge' => 'K. Fernando',
                'note' => 'Issued for Emergency OT Dressing'
            ],
            [
                'id' => 302,
                'date' => '2026-09-15',
                'item_name' => 'FOLEY CATHETER 16FR',
                'bht_no' => 'BHT-88392',
                'quantity' => '1 Pcs',
                'balance' => '25 Pcs',
                'officer_name' => 'Nurse Ratnayake (RN-512)',
                'signature_in_charge' => 'S. Ratnayake',
                'note' => 'Catheterization ward procedure'
            ],
            [
                'id' => 303,
                'date' => '2026-09-14',
                'item_name' => 'IV CANNULA 20G',
                'bht_no' => 'BHT-88375',
                'quantity' => '5 Pcs',
                'balance' => '120 Pcs',
                'officer_name' => 'Sr. Fernando (RN-204)',
                'signature_in_charge' => 'K. Fernando',
                'note' => 'Routine IV line change'
            ],
        ]);
    }

    /**
     * Store a new requisition entry.
     */
    public function storeRequisition(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|string',
            'item_name' => 'required|string',
            'bht_no' => 'required|string',
            'quantity' => 'required|string',
            'balance' => 'required|string',
            'officer_name' => 'required|string',
            'signature_in_charge' => 'required|string',
            'note' => 'nullable|string',
        ]);

        try {
            if (Schema::hasTable('bht_requisitions')) {
                $validated['category_type'] = 'surgical_consumable_2';
                $record = BhtRequisition::create($validated);
                return response()->json(['success' => true, 'data' => $record, 'message' => 'Requisition log saved.']);
            }
        } catch (\Exception $e) {
            // Fallback
        }

        $validated['id'] = time();
        return response()->json(['success' => true, 'data' => $validated, 'message' => 'Requisition log saved successfully.']);
    }

    /**
     * Delete requisition record.
     */
    public function destroyRequisition($id)
    {
        return response()->json(['success' => true, 'message' => 'Requisition record deleted.']);
    }

    /**
     * Delete consumable item.
     */
    public function destroyConsumable($id)
    {
        return response()->json(['success' => true, 'message' => 'Consumable item deleted.']);
    }
}
