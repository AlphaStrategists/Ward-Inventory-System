<?php

namespace App\Http\Controllers;

use App\Models\MedicineBatch;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\StockReceipt;
use App\Models\Supplier;
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
        $userRole = strtolower(trim((string) ($user->role ?? '')));
        $statusFilter = $request->query('status');

        // Default role-based filtering if no explicit filter is passed:
        // Nurses: Show all their requested orders.
        // Ms: Show all orders across all medicines where ms_approval is 'Pending'.
        // Pharmacists: Show all orders where ms_approval is 'Approved' but pending issue.
        if (!$statusFilter) {
            if (in_array($user->role, ['Nurse', 'Head Nurse']) || $user->hasRole(['Nurse', 'Head Nurse']) || in_array($userRole, ['nurse', 'head nurse'], true)) {
                $statusFilter = 'my_orders';
            } elseif ($user->hasRole('MS') || $userRole === 'ms') {
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
            'medicine.batches.supplier'
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

        $suppliers = \App\Models\Supplier::orderBy('supplier_name')->get();

        return view('orders.index', compact('orderDetails', 'statusFilter', 'counts', 'suppliers'));
    }

    /**
     * Approve or Reject an order requisition (Ms action).
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
            $orderUpdates = [
                'ms_approval_status' => 'Approved',
                'approved_by' => Auth::id() ?? 1,
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'approved_at')) {
                $orderUpdates['approved_at'] = now();
            }
            $order->update($orderUpdates);

            return redirect()->back()->with('success', "Order requisition {$order->req_no} has been approved.");
        } else {
            $orderUpdates = [
                'ms_approval_status' => 'Rejected',
                'approved_by' => Auth::id() ?? 1,
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'approved_at')) {
                $orderUpdates['approved_at'] = now();
            }
            $order->update($orderUpdates);

            return redirect()->back()->with('success', "Order requisition {$order->req_no} has been rejected.");
        }
    }

    /**
     * Approve all pending requisitions in bulk (Ms action).
     */
    public function approveAll(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            abort(401);
        }

        $isMs = $currentUser->hasRole('MS') || strtolower(trim((string) ($currentUser->role ?? ''))) === 'ms';
        if (!$isMs) {
            return redirect()->back()->with('error', 'Only Ms can approve requisitions.');
        }

        $userId = Auth::id() ?? 1;

        $bulkUpdates = [
            'ms_approval_status' => 'Approved',
            'approved_by' => $userId,
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'approved_at')) {
            $bulkUpdates['approved_at'] = now();
        }

        // Efficient bulk update in a single database query
        $updatedCount = Order::where('ms_approval_status', 'Pending')->update($bulkUpdates);

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
            'qty_issued' => 'required|numeric|min:1|max:' . $detail->qty_requested,
        ], [
            'qty_issued.max' => 'Issued quantity cannot exceed the requested/approved quantity (' . $detail->qty_requested . ').',
        ]);

        $qtyIssued = (int) $validated['qty_issued'];

        $detail->update([
            'qty_issued' => $qtyIssued,
            'remark' => '[ISSUED_BY:' . (Auth::user()->name ?? 'Pharmacist') . '] ' . ($detail->remark ?? '')
        ]);

        if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'issued_by')) {
            $order->update([
                'issued_by' => Auth::id() ?? 1,
                'issued_date' => now(),
            ]);
        }

        return redirect()->back()->with('success', "Order requisition {$order->req_no} has been issued successfully.");
    }

    /**
     * Confirm receipt of issued medicine into ward inventory (Nurse action).
     */
    public function receive(Request $request, $id)
    {
        $detail = OrderDetail::with(['order', 'medicine.strengthUnit', 'medicine.form', 'medicine.unit'])->findOrFail($id);
        $order = $detail->order;

        if (strtolower($order->ms_approval_status ?? '') === 'cancelled') {
            return redirect()->back()->with('error', 'Cannot receive a cancelled order.');
        }

        if (str_contains($detail->remark ?? '', '[RECEIVED')) {
            return redirect()->back()->with('error', 'This order has already been received.');
        }

        // Inherit supplier_id directly from the original order record
        $orderSupplierId = $order->supplier_id ?? $detail->supplier_id ?? null;
        if (!$orderSupplierId && preg_match('/\[SUPPLIER:(.*?)\]/', $detail->remark ?? '', $supMatches)) {
            $supplierName = trim($supMatches[1]);
            $orderSupplierId = Supplier::whereRaw('LOWER(TRIM(supplier_name)) = ?', [strtolower($supplierName)])->value('id');
        }
        if (!$orderSupplierId) {
            $orderSupplierId = MedicineBatch::where('medicine_id', $detail->medicine_id)
                ->whereNotNull('supplier_id')
                ->orderByDesc('id')
                ->value('id');
        }

        $isNewBatch = $request->input('batch_type') === 'new';

        if ($isNewBatch) {
            $validated = $request->validate([
                'new_batch_no' => 'required|string|max:100',
                'new_expiry_date' => 'required|date',
            ]);

            $batch = MedicineBatch::create([
                'medicine_id' => $detail->medicine_id,
                'supplier_id' => $orderSupplierId,
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

            if ($orderSupplierId) {
                MedicineBatch::where('id', $batchId)->whereNull('supplier_id')->update([
                    'supplier_id' => $orderSupplierId,
                ]);
            }
        }

        $wardId = Auth::user()->ward_id ?? DB::table('wards')->value('id');
        $userId = Auth::id() ?? 1;

        $qtyReceived = (int) ($request->input('qty_received') ?: $detail->qty_issued);
        $medicine = $detail->medicine;
        $ledgerQtyReceived = $qtyReceived;
        if ($medicine && $medicine->isLiquidMl()) {
            $bottleVolume = $medicine->getBottleVolumeMl();
            if ($bottleVolume > 0) {
                $ledgerQtyReceived = $qtyReceived * $bottleVolume;
            }
        }

        if ($ledgerQtyReceived > 0) {
            StockReceipt::create([
                'batch_id' => $batchId,
                'ward_id' => $wardId,
                'quantity_received' => $ledgerQtyReceived,
                'received_by' => $userId,
                'date' => now(),
            ]);
        }

        $remarkStr = $detail->remark ?? '';

        $detailUpdates = [
            'remark' => trim('[RECEIVED_BY:' . (Auth::user()->name ?? 'Nurse') . '] ' . $remarkStr)
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('order_details', 'qty_received')) {
            $detailUpdates['qty_received'] = $qtyReceived;
        }
        $detail->update($detailUpdates);

        if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'received_by')) {
            $order->update([
                'received_by' => $userId,
                'received_date' => now(),
            ]);
        }

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

        $isNurse = in_array($currentUser->role, ['Nurse', 'Head Nurse']) || $currentUser->hasRole(['Nurse', 'Head Nurse']) || in_array(strtolower($currentUser->role ?? ''), ['nurse', 'head nurse'], true);
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
