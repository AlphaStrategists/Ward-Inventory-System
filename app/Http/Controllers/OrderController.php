<?php

namespace App\Http\Controllers;

use App\Models\MedicineBatch;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\StockReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a centralized listing of all pharmacy orders and requisitions.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRole = strtolower($user->role ?? '');
        $statusFilter = $request->query('status');

        // Default role-based filtering if no explicit filter is passed:
        // Nurses: Show all their requested orders.
        // Doctors: Show all orders across all medicines where ms_approval is 'Pending'.
        // Pharmacists: Show all orders where ms_approval is 'Approved' but pending issue.
        if (!$statusFilter) {
            if ($user->hasRole('Nurse') || $userRole === 'nurse') {
                $statusFilter = 'my_orders';
            } elseif ($user->hasRole('Doctor') || $userRole === 'doctor') {
                $statusFilter = 'pending';
            } elseif ($user->hasRole('Pharmacist') || $userRole === 'pharmacist') {
                $statusFilter = 'pending_issue';
            } else {
                $statusFilter = 'all';
            }
        }

        $query = OrderDetail::with([
            'order.requester',
            'order.approver',
            'order.ward',
            'medicine.unit',
            'medicine.form',
            'medicine.category',
            'medicine.batches'
        ]);

        // Apply tab / status filter
        if ($statusFilter === 'my_orders') {
            $query->whereHas('order', fn($q) => $q->where('requested_by', $user->id));
        } elseif ($statusFilter === 'pending') {
            $query->whereHas('order', fn($q) => $q->where('ms_approval_status', 'Pending'));
        } elseif ($statusFilter === 'pending_issue') {
            $query->whereHas('order', fn($q) => $q->where('ms_approval_status', 'Approved'))
                  ->where('qty_issued', 0);
        } elseif ($statusFilter === 'pending_receipt') {
            $query->whereHas('order', fn($q) => $q->where('ms_approval_status', 'Approved'))
                  ->where('order_details.qty_issued', '>', 0)
                  ->where(function ($q) {
                      $q->whereNull('order_details.remark')
                        ->orWhere('order_details.remark', 'not like', '%[RECEIVED%');
                  });
        } elseif ($statusFilter === 'approved') {
            $query->whereHas('order', fn($q) => $q->where('ms_approval_status', 'Approved'));
        } elseif ($statusFilter === 'rejected') {
            $query->whereHas('order', fn($q) => $q->where('ms_approval_status', 'Rejected'));
        } elseif ($statusFilter === 'cancelled') {
            $query->whereHas('order', fn($q) => $q->where('ms_approval_status', 'Cancelled'));
        } elseif ($statusFilter === 'received') {
            $query->where('order_details.remark', 'like', '%[RECEIVED%');
        }

        // Search filter (Req No, Medicine Name, Requester Name)
        if ($request->filled('search')) {
            $search = strtolower($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', fn($sq) => $sq->whereRaw('LOWER(req_no) LIKE ?', ["%{$search}%"]))
                  ->orWhereHas('medicine', fn($sq) => $sq->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]))
                  ->orWhereHas('order.requester', fn($sq) => $sq->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]));
            });
        }

        // Date filter
        if ($request->filled('date')) {
            $query->whereHas('order', fn($q) => $q->whereDate('date', $request->query('date')));
        }

        $orderDetails = $query->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->orderBy('orders.date', 'desc')
            ->orderBy('orders.id', 'desc')
            ->select('order_details.*')
            ->get();

        // Calculate badges count
        $counts = [
            'all' => OrderDetail::count(),
            'my_orders' => OrderDetail::whereHas('order', fn($q) => $q->where('requested_by', $user->id))->count(),
            'pending' => OrderDetail::whereHas('order', fn($q) => $q->where('ms_approval_status', 'Pending'))->count(),
            'pending_issue' => OrderDetail::whereHas('order', fn($q) => $q->where('ms_approval_status', 'Approved'))->where('order_details.qty_issued', 0)->count(),
            'pending_receipt' => OrderDetail::whereHas('order', fn($q) => $q->where('ms_approval_status', 'Approved'))->where('order_details.qty_issued', '>', 0)->where(function ($q) {
                $q->whereNull('order_details.remark')->orWhere('order_details.remark', 'not like', '%[RECEIVED%');
            })->count(),
            'received' => OrderDetail::where('order_details.remark', 'like', '%[RECEIVED%')->count(),
            'cancelled' => OrderDetail::whereHas('order', fn($q) => $q->where('ms_approval_status', 'Cancelled'))->count(),
        ];

        return view('orders.index', compact('orderDetails', 'statusFilter', 'counts'));
    }

    /**
     * Approve or Reject an order requisition (Doctor action).
     */
    public function approve(Request $request, $id)
    {
        $detail = OrderDetail::with('order')->findOrFail($id);
        $order = $detail->order;

        if (strtolower($order->ms_approval_status ?? '') === 'cancelled') {
            return redirect()->back()->with('error', 'Cannot process a cancelled order.');
        }

        if ($request->input('action') === 'Approve') {
            $validated = $request->validate([
                'qty_requested' => 'required|integer|min:1',
            ]);

            $detail->update(['qty_requested' => $validated['qty_requested']]);
            $order->update([
                'ms_approval_status' => 'Approved',
                'approved_by' => Auth::id() ?? 1,
            ]);

            return redirect()->back()->with('success', "Order requisition {$order->req_no} has been approved.");
        } else {
            $order->update([
                'ms_approval_status' => 'Rejected',
                'approved_by' => Auth::id() ?? 1,
            ]);

            return redirect()->back()->with('success', "Order requisition {$order->req_no} has been rejected.");
        }
    }

    /**
     * Approve all pending requisitions in bulk (Doctor action).
     */
    public function approveAll(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            abort(401);
        }

        $isDoctor = $currentUser->hasRole('Doctor') || strtolower($currentUser->role ?? '') === 'doctor';
        if (!$isDoctor) {
            return redirect()->back()->with('error', 'Only doctors can approve requisitions.');
        }

        $userId = Auth::id() ?? 1;

        // Efficient bulk update in a single database query
        $updatedCount = Order::where('ms_approval_status', 'Pending')->update([
            'ms_approval_status' => 'Approved',
            'approved_by' => $userId,
        ]);

        return redirect()->back()->with('success', "All pending order requisition(s) ({$updatedCount}) have been approved.");
    }

    /**
     * Issue requested batch from pharmacy (Pharmacist action).
     */
    public function issue(Request $request, $id)
    {
        $detail = OrderDetail::with('order')->findOrFail($id);
        $order = $detail->order;

        if (strtolower($order->ms_approval_status ?? '') === 'cancelled') {
            return redirect()->back()->with('error', 'Cannot issue a cancelled order.');
        }

        $validated = $request->validate([
            'qty_issued' => 'required|integer|min:1',
        ]);

        $qtyIssued = $validated['qty_issued'];
        if ($qtyIssued > $detail->qty_requested) {
            return redirect()->back()->with('error', 'Issued quantity cannot exceed requested quantity.');
        }

        $detail->update([
            'qty_issued' => $qtyIssued,
            'remark' => '[ISSUED_BY:' . (Auth::user()->name ?? 'Pharmacist') . '] ' . ($detail->remark ?? '')
        ]);

        return redirect()->back()->with('success', "Order requisition {$order->req_no} has been issued successfully.");
    }

    /**
     * Confirm receipt of issued medicine into ward inventory (Nurse action).
     */
    public function receive(Request $request, $id)
    {
        $detail = OrderDetail::with(['order', 'medicine'])->findOrFail($id);
        $order = $detail->order;

        if (strtolower($order->ms_approval_status ?? '') === 'cancelled') {
            return redirect()->back()->with('error', 'Cannot receive a cancelled order.');
        }

        if (str_contains($detail->remark ?? '', '[RECEIVED')) {
            return redirect()->back()->with('error', 'This order has already been received.');
        }

        $isNewBatch = $request->input('batch_type') === 'new';

        if ($isNewBatch) {
            $validated = $request->validate([
                'new_batch_no' => 'required|string|max:100',
                'new_expiry_date' => 'required|date',
            ]);

            $batch = MedicineBatch::create([
                'medicine_id' => $detail->medicine_id,
                'batch_no' => strtoupper(trim($validated['new_batch_no'])),
                'expiry_date' => $validated['new_expiry_date'],
                'created_at' => now(),
            ]);
            $batchId = $batch->id;
        } else {
            $validated = $request->validate([
                'batch_id' => 'required|integer|exists:medicine_batches,id',
            ]);
            $batchId = $validated['batch_id'];
        }

        $wardId = Auth::user()->ward_id ?? DB::table('wards')->value('id');
        $userId = Auth::id() ?? 1;

        if ($detail->qty_issued > 0) {
            StockReceipt::create([
                'batch_id' => $batchId,
                'ward_id' => $wardId,
                'quantity_received' => $detail->qty_issued,
                'received_by' => $userId,
                'date' => now(),
            ]);
        }

        $detail->update([
            'remark' => trim('[RECEIVED_BY:' . (Auth::user()->name ?? 'Nurse') . '] ' . ($detail->remark ?? ''))
        ]);

        return redirect()->back()->with('success', "Order requisition {$order->req_no} received successfully. Stock has been incremented.");
    }

    /**
     * Cancel a pending requisition (Nurse creator action).
     */
    public function cancel(Request $request, $id)
    {
        $detail = OrderDetail::find($id);
        $order = $detail ? $detail->order : Order::findOrFail($id);

        $currentUser = Auth::user();
        if (!$currentUser) {
            abort(401);
        }

        $isNurse = $currentUser->hasRole('Nurse') || strtolower($currentUser->role ?? '') === 'nurse';
        if (!$isNurse || (int) $order->requested_by !== (int) $currentUser->id) {
            return redirect()->back()->with('error', 'Only the nurse who created this order can cancel it.');
        }

        if (strtolower($order->ms_approval_status ?? '') !== 'pending') {
            return redirect()->back()->with('error', 'Only pending orders can be cancelled.');
        }

        $order->update([
            'ms_approval_status' => 'Cancelled',
        ]);

        return redirect()->back()->with('success', "Order requisition {$order->req_no} has been cancelled.");
    }
}
