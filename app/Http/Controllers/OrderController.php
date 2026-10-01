<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Store a new pharmacy requisition order in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'qty_requested' => 'required|integer|min:1',
            'ward_id' => 'required|exists:wards,id',
            'requested_by' => 'required|exists:users,id',
            'remark' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, &$order) {
            // Generate unique requisition number
            $reqNo = 'REQ-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

            // 1. Create order record
            $order = Order::create([
                'ward_id' => $validated['ward_id'],
                'req_no' => $reqNo,
                'date' => now(),
                'requested_by' => $validated['requested_by'],
                'ms_approval_status' => 'Pending',
                'approved_by' => null,
                'remark' => $validated['remark'] ?? null,
            ]);

            // 2. Create order detail record
            OrderDetail::create([
                'order_id' => $order->id,
                'medicine_id' => $validated['medicine_id'],
                'qty_requested' => $validated['qty_requested'],
                'qty_issued' => 0,
                'remark' => $validated['remark'] ?? null,
            ]);
        });

        return redirect()->back()->with('success', 'Pharmacy requisition order created successfully! Requisition No: ' . ($order->req_no ?? ''));
    }

    /**
     * Update MS approval status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'ms_approval_status' => 'required|in:Pending,Completed,Approved,Rejected',
            'approved_by' => 'nullable|exists:users,id',
        ]);

        $msOfficerId = $request->approved_by 
            ?? $order->approved_by 
            ?? \App\Models\User::whereHas('role', function ($q) {
                $q->where('role_name', 'like', '%MS%');
            })->first()?->id;

        $order->update([
            'ms_approval_status' => $request->ms_approval_status,
            'approved_by' => $msOfficerId,
        ]);

        return redirect()->back()->with('success', 'Order requisition status successfully updated to "' . $request->ms_approval_status . '"!');
    }
}
