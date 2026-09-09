<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\RequestedStock;
use App\Models\Staff;
use Illuminate\Http\Request;

class RequestedStockController extends Controller
{
    public function index(Request $request)
    {
        $query = RequestedStock::with([
            'item',
            'requestedBy',
            'confirmRequestBy',
            'approvedByMs',
            'issuedOfficer',
            'confirmReceivedBy',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        $requests = $query->orderBy('request_id', 'desc')->get();
        $items = Item::whereIn('workflow_pattern', ['request_simple', 'request_approved'])->get();
        $staffList = Staff::orderBy('name')->get();
        $msStaffList = Staff::where('role', 'MS Officer')->get();

        return view('requested-stock.index', compact('requests', 'items', 'staffList', 'msStaffList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,item_id',
            'requested_by_staff_id' => 'required|exists:staff,staff_id',
            'request_date' => 'required|date',
            'required_quantity' => 'required|integer|min:1',
            'confirm_request_staff_id' => 'nullable|exists:staff,staff_id',
        ]);

        $item = Item::findOrFail($validated['item_id']);

        if (!in_array($item->workflow_pattern, ['request_simple', 'request_approved'])) {
            return redirect()->back()->withErrors(['item_id' => 'Selected item does not support request workflows.']);
        }

        $status = ($item->quantity < 15) ? 'low_stock' : 'pending';

        RequestedStock::create([
            'item_id' => $item->item_id,
            'requested_by_staff_id' => $validated['requested_by_staff_id'],
            'request_date' => $validated['request_date'],
            'required_quantity' => $validated['required_quantity'],
            'balance_before' => $item->quantity,
            'status' => $status,
            'confirm_request_staff_id' => $validated['confirm_request_staff_id'] ?? $validated['requested_by_staff_id'],
        ]);

        return redirect()->back()->with('success', 'Stock request created successfully.');
    }

    public function approveMs(Request $request, $id)
    {
        $stockRequest = RequestedStock::with('item')->findOrFail($id);

        if ($stockRequest->item->workflow_pattern !== 'request_approved') {
            return redirect()->back()->withErrors(['error' => 'This request pattern does not require MS approval.']);
        }

        $validated = $request->validate([
            'approved_by_ms_staff_id' => 'required|exists:staff,staff_id',
        ]);

        $stockRequest->update([
            'approved_by_ms_staff_id' => $validated['approved_by_ms_staff_id'],
            'status' => 'approved',
        ]);

        return redirect()->back()->with('success', 'Request approved by MS Officer.');
    }

    public function issue(Request $request, $id)
    {
        $stockRequest = RequestedStock::with('item')->findOrFail($id);

        if ($stockRequest->item->workflow_pattern !== 'request_approved') {
            return redirect()->back()->withErrors(['error' => 'This request pattern does not require issuing officer sign-off.']);
        }

        $validated = $request->validate([
            'issued_officer_staff_id' => 'required|exists:staff,staff_id',
            'issued_date' => 'required|date',
        ]);

        $stockRequest->update([
            'issued_officer_staff_id' => $validated['issued_officer_staff_id'],
            'issued_date' => $validated['issued_date'],
            'status' => 'issued',
        ]);

        return redirect()->back()->with('success', 'Request marked as issued by issuing officer.');
    }

    public function receive(Request $request, $id)
    {
        $stockRequest = RequestedStock::with('item')->findOrFail($id);

        $validated = $request->validate([
            'received_quantity' => 'required|integer|min:1',
            'received_date' => 'required|date',
            'confirm_received_staff_id' => 'required|exists:staff,staff_id',
        ]);

        $stockRequest->update([
            'received_quantity' => $validated['received_quantity'],
            'received_date' => $validated['received_date'],
            'confirm_received_staff_id' => $validated['confirm_received_staff_id'],
            'status' => 'completed',
        ]);

        // Increment stock balance
        $stockRequest->item->increment('quantity', $validated['received_quantity']);

        return redirect()->back()->with('success', 'Stock received and inventory updated successfully.');
    }
}
