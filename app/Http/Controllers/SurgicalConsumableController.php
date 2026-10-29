<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\StockLedger;
use App\Models\StockReceipt;
use App\Services\WardDatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurgicalConsumableController extends Controller
{
    /**
     * Fallback default consumables 1
     */
    private $defaultConsumables = [
        ['name' => 'SURGICAL GLOVES 7.5', 'balance' => 30, 'unit' => 'Box'],
        ['name' => 'COTTON WOOL 500G', 'balance' => 15, 'unit' => 'Roll'],
        ['name' => 'GAUZE BANDAGE 10CM', 'balance' => 45, 'unit' => 'Roll'],
        ['name' => 'CREPE BANDAGE 7.5CM', 'balance' => 20, 'unit' => 'Pcs'],
        ['name' => 'SURGICAL MASK 3-PLY', 'balance' => 100, 'unit' => 'Box'],
        ['name' => 'POVIDONE IODINE 500ML', 'balance' => 12, 'unit' => 'Bottle'],
        ['name' => 'ADHESIVE PLASTER 5CM', 'balance' => 25, 'unit' => 'Roll'],
    ];

    /**
     * Category ID for Surgical Consumable 1
     */
    protected $categoryId = 1;

    /**
     * Render the Surgical Consumable 1 front-end view.
     */
    public function index(Request $request, $role = null)
    {
        // Get the authenticated user's role from RoleMiddleware.
        // Do NOT take the role from the URL or query string.
        $activeRole = $request->attributes->get('active_role');

        if (!$activeRole) {
            abort(403, 'Authenticated user role could not be determined.');
        }

        $activeRole = strtolower(trim((string) $activeRole));

        // Get the currently authenticated user.
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $currentUserId = (int) $user->id;
        $currentUserName = (string) $user->name;

        return view('surgical-consumable-1', [
            'activeRole' => $activeRole,
            'currentUserId' => $currentUserId,
            'currentUserName' => $currentUserName,
        ]);
    }

    /**
     * Get list of surgical consumables 1 from general_items table.
     */
    public function getConsumables()
    {
        $items = WardDatabaseService::getCategoryItems($this->categoryId, $this->defaultConsumables);
        return response()->json($items);
    }

    /**
     * Store a new surgical consumable item into general_items table.
     */
    public function storeConsumable(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'balance' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
        ]);

        $item = WardDatabaseService::storeItem(
            $this->categoryId,
            $validated['name'],
            $validated['balance'],
            $validated['unit']
        );

        return response()->json([
            'success' => true,
            'data' => $item,
            'message' => 'Consumable item added and updated to database successfully.'
        ], 201);
    }

    /**
     * Update an existing consumable item in general_items table.
     */
    public function updateConsumable(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
        ]);

        WardDatabaseService::updateItem($id, $validated['name']);

        return response()->json([
            'success' => true,
            'message' => 'Consumable item updated in database successfully.'
        ]);
    }

    /**
     * Delete a consumable item from general_items table.
     */
    public function destroyConsumable($id)
    {
        WardDatabaseService::deleteItem($id);

        return response()->json([
            'success' => true,
            'message' => 'Consumable item removed from database.'
        ]);
    }

    /**
     * Helper to verify active role against allowed roles.
     */
    private function verifyRole(Request $request, array $allowedRoles): string
    {
        $activeRole = $request->attributes->get('active_role');

        if (!$activeRole) {
            abort(response()->json([
                'success' => false,
                'message' => 'Authenticated user role could not be determined.',
            ], 403));
        }

        $activeRole = strtolower(trim((string) $activeRole));

        $allowedRoles = array_map(
            fn ($role) => strtolower(trim((string) $role)),
            $allowedRoles
        );

        if (
            $activeRole === 'admin' ||
            in_array($activeRole, $allowedRoles, true)
        ) {
            return $activeRole;
        }

        abort(response()->json([
            'success' => false,
            'message' => "Unauthorized: Role '{$activeRole}' is not permitted.",
        ], 403));
    }

        /**
         * Ensure master lookup records exist across units, forms, medicines, etc.
         */
        private function ensureLookupData()
        {
            WardDatabaseService::ensureMasterRecords();

            try {
                DB::table('units')->insertOrIgnore(['id' => 1, 'unit_name' => 'Box']);
                DB::table('units')->insertOrIgnore(['id' => 2, 'unit_name' => 'Roll']);
                DB::table('units')->insertOrIgnore(['id' => 3, 'unit_name' => 'Pcs']);
                DB::table('units')->insertOrIgnore(['id' => 4, 'unit_name' => 'Bottle']);
                DB::table('units')->insertOrIgnore(['id' => 5, 'unit_name' => 'Kit']);
                DB::table('medicine_forms')->insertOrIgnore(['id' => 1, 'form_name' => 'Consumable']);
            } catch (\Exception $e) {
                // Ignore
            }
        }

    /**
     * Ensure a medicine record exists in `medicines` table.
     */
    private function ensureMedicineRecord($name, $categoryId, $unitName = 'Box')
    {
        $this->ensureLookupData();
        $name = strtoupper(trim($name));

        $unit = DB::table('units')->where('unit_name', $unitName)->first();
        $unitId = $unit ? $unit->id : 1;

        $med = DB::table('medicines')->where('name', $name)->first();
        if ($med) {
            return $med->id;
        }

        $itemCode = sprintf('MED-%d-%d-%03d', $categoryId, time(), mt_rand(10, 99));

        return DB::table('medicines')->insertGetId([
            'item_code' => $itemCode,
            'name' => $name,
            'category_id' => $categoryId,
            'unit_id' => $unitId,
            'form_id' => 1,
            'min_level' => 10,
            'warning_limit' => 20,
            'created_at' => now(),
        ]);
    }

    /**
     * Ensure a medicine batch exists in `medicine_batches` table.
     */
    private function ensureMedicineBatch($medicineId, $batchNo = null)
    {
        $this->ensureLookupData();
        $batch = DB::table('medicine_batches')->where('medicine_id', $medicineId)->first();
        if ($batch) {
            return $batch->id;
        }

        $batchNo = $batchNo ?? ('BATCH-' . date('Ymd') . '-' . mt_rand(100, 999));

        return DB::table('medicine_batches')->insertGetId([
            'medicine_id' => $medicineId,
            'supplier_id' => null,
            'batch_no' => $batchNo,
            'expiry_date' => date('Y-m-d', strtotime('+2 years')),
            'created_at' => now(),
        ]);
    }

    /**
     * Ensure general item exists in `general_items`.
     */
    private function ensureGeneralItem($name, $categoryId, $unitName = 'Box')
    {
        $name = strtoupper(trim($name));
        $item = DB::table('general_items')
            ->where('name', $name)
            ->where('category_id', $categoryId)
            ->first();

        if ($item) {
            return $item->id;
        }

        return DB::table('general_items')->insertGetId([
            'item_code' => sprintf('CAT%d-%d', $categoryId, time()),
            'name' => $name,
            'category_id' => $categoryId,
        ]);
    }

    /**
     * Resolve the real logged-in user supplied by the main login system.
     *
     * This module does NOT create or manage login accounts. It expects the
     * partner login system to expose the authenticated user through Laravel
     * authentication.
     */
    private function currentUser(Request $request): array
{
    $user = $request->user();

    if (!$user) {
        throw new \RuntimeException(
            'No authenticated user found.'
        );
    }

    return [
        'id' => (int) $user->id,
        'name' => (string) $user->name,
    ];
}

    /**
     * Get current ward stock for one general item.
     */
    private function getCurrentBalance(string $itemName): int
    {
        $item = DB::table('general_items')
            ->where('category_id', $this->categoryId)
            ->where('name', strtoupper(trim($itemName)))
            ->first();

        if (!$item) {
            return 0;
        }

        $received = (int) DB::table('general_transactions')
            ->where('item_id', $item->id)
            ->sum('quantity_received');

        $issued = (int) DB::table('general_transactions')
            ->where('item_id', $item->id)
            ->sum('quantity_issued');

        return max(0, $received - $issued);
    }

    /**
     * Log a workflow event against the real logged-in user.
     */
    private function logWorkflowEvent($transactionId, $event, array $data, ?int $userId = null)
    {
        WardDatabaseService::ensureMasterRecords();

        $actorId = $userId;

        if (!$actorId) {
            try {
                $actor = $this->currentUser(request());
                $actorId = $actor['id'];
            } catch (\Throwable $e) {
                $actorId = null;
            }
        }

        if (!$actorId) {
            return;
        }

        DB::table('activity_logs')->insert([
            'user_id' => $actorId,
            'action_performed' => "REQWF|{$transactionId}|{$event}|" . json_encode($data),
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);
    }

    /**
     * Load workflow events from activity_logs.
     */
    private function loadWorkflowEvents()
    {
        $logs = DB::table('activity_logs')
            ->where('action_performed', 'like', 'REQWF|%')
            ->orderBy('id', 'asc')
            ->get();

        $events = [];

        foreach ($logs as $log) {
            $parts = explode('|', $log->action_performed, 4);

            if (count($parts) === 4) {
                $txId = (int) $parts[1];
                $eventName = $parts[2];
                $data = json_decode($parts[3], true) ?: [];
                $events[$txId][$eventName] = $data;
            }
        }

        return $events;
    }

    /**
     * Get all requisitions for Surgical Consumable 1.
     *
     * Workflow:
     * Nurse creates -> MS approves/edits -> Nurse receives.
     */
    public function getRequisitions()
    {
        $this->ensureLookupData();

        $results = [];

        try {
            $orders = DB::table('orders')
                ->leftJoin('users as requester', 'orders.requested_by', '=', 'requester.id')
                ->leftJoin('users as approver', 'orders.approved_by', '=', 'approver.id')
                ->where('orders.ward_id', 1)
                ->orderByDesc('orders.id')
                ->select(
                    'orders.*',
                    'requester.name as requester_name',
                    'approver.name as approver_name'
                )
                ->get();

            foreach ($orders as $order) {
                $meta = json_decode($order->remark, true) ?: [];

                $categoryId = (int) ($meta['category_id'] ?? $this->categoryId);

                if ($categoryId !== (int) $this->categoryId) {
                    continue;
                }

                $details = DB::table('order_details')
                    ->leftJoin('medicines', 'order_details.medicine_id', '=', 'medicines.id')
                    ->where('order_details.order_id', $order->id)
                    ->select(
                        'order_details.id',
                        'order_details.medicine_id',
                        'medicines.name as medicine_name',
                        'order_details.qty_requested',
                        'order_details.qty_issued',
                        'order_details.remark as unit'
                    )
                    ->get();

                $items = [];

                foreach ($details as $detail) {
                    $unit = $detail->unit ?: 'Box';
                    $receivedQty = (int) ($detail->qty_issued ?? 0);

                    $savedItem = collect($meta['items'] ?? [])
                        ->first(function ($item) use ($detail) {
                            return isset($item['id']) && (int) $item['id'] === (int) $detail->id;
                        });

                    $items[] = [
                        'id' => (int) $detail->id,
                        'medicine_id' => (int) $detail->medicine_id,
                        'name' => $detail->medicine_name
                            ?: ($savedItem['name'] ?? 'SURGICAL ITEM'),
                        'requested_qty_num' => (int) $detail->qty_requested,
                        'requested_qty' => (int) $detail->qty_requested . ' ' . $unit,
                        'received_qty_num' => $receivedQty,
                        'received_qty' => $receivedQty > 0
                            ? $receivedQty . ' ' . $unit
                            : '',
                        'unit' => $unit,
                        'balance' => $savedItem['balance'] ?? ('0 ' . $unit),
                    ];
                }

                // If an old order has no order_details, use the stored JSON item.
                if (empty($items) && !empty($meta['items'])) {
                    foreach ($meta['items'] as $item) {
                        $unit = $item['unit'] ?? 'Box';
                        $requestedQty = (int) ($item['requested_qty_num'] ?? 0);
                        $receivedQty = (int) ($item['received_qty_num'] ?? 0);

                        $items[] = [
                            'id' => (int) ($item['id'] ?? 0),
                            'medicine_id' => (int) ($item['medicine_id'] ?? 0),
                            'name' => $item['name'] ?? 'SURGICAL ITEM',
                            'requested_qty_num' => $requestedQty,
                            'requested_qty' => $requestedQty . ' ' . $unit,
                            'received_qty_num' => $receivedQty,
                            'received_qty' => $receivedQty > 0
                                ? $receivedQty . ' ' . $unit
                                : '',
                            'unit' => $unit,
                            'balance' => $item['balance'] ?? ('0 ' . $unit),
                        ];
                    }
                }

                $workflowStatus = strtolower(
                    (string) ($meta['workflow_status'] ?? $order->ms_approval_status ?? 'pending')
                );

                if ($order->ms_approval_status === 'Rejected') {
                    $workflowStatus = 'rejected';
                } elseif ($meta['workflow_status'] === 'received') {
                    $workflowStatus = 'received';
                } elseif ($order->ms_approval_status === 'Approved' && $workflowStatus === 'pending') {
                    $workflowStatus = 'approved';
                }

                $firstItem = $items[0] ?? null;

                $results[] = [
                    'id' => (int) $order->id,
                    'order_id' => (int) $order->id,
                    'req_no' => $order->req_no,
                    'date' => date('Y-m-d H:i:s', strtotime($order->date)),
                    'status' => $workflowStatus,
                    'ms_approval_status' => $order->ms_approval_status,
                    'supply_name' => $firstItem['name'] ?? 'SURGICAL CONSUMABLE',
                    'balance' => $firstItem['balance'] ?? '-',
                    'requested_qty' => $firstItem['requested_qty'] ?? '-',
                    'received_qty' => $firstItem['received_qty'] ?? '',
                    'requested_by' => $order->requester_name
                        ?? $meta['requested_by_name']
                        ?? '-',
                    'requested_by_id' => (int) $order->requested_by,
                    'approved_by' => $order->approver_name
                        ?? $meta['approved_by_name']
                        ?? '',
                    'approved_by_id' => $order->approved_by
                        ? (int) $order->approved_by
                        : null,
                    'received_by' => $meta['received_by_name'] ?? '',
                    'received_by_id' => isset($meta['received_by_id'])
                        ? (int) $meta['received_by_id']
                        : null,
                    'rejection_reason' => $meta['rejection_reason'] ?? '',
                    'items' => $items,
                    'can_delete' => $workflowStatus === 'pending',
                ];
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json($results);
    }

    /**
     * STEP 1: Nurse creates a requisition.
     */
    public function storeRequisition(Request $request)
    {
        $this->verifyRole($request, ['nurse', 'admin']);
        $this->ensureLookupData();

        $actor = $this->currentUser($request);

        $validated = $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:150',
            'items.*.requested_qty_num' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
        ]);

        $orderDate = date('Y-m-d H:i:s', strtotime($validated['date']));

        $items = [];

        foreach ($validated['items'] as $inputItem) {
            $name = strtoupper(trim($inputItem['name']));
            $unit = trim($inputItem['unit'] ?: 'Box');
            $qty = (int) $inputItem['requested_qty_num'];

            $this->ensureGeneralItem($name, $this->categoryId, $unit);

            $currentBalance = $this->getCurrentBalance($name);

            $items[] = [
                'name' => $name,
                'requested_qty_num' => $qty,
                'requested_qty' => $qty . ' ' . $unit,
                'received_qty_num' => 0,
                'received_qty' => '',
                'unit' => $unit,
                'balance' => $currentBalance . ' ' . $unit,
            ];
        }

        $reqNo = sprintf(
            'REQ-%d-%s-%04d',
            $this->categoryId,
            date('YmdHis'),
            random_int(1000, 9999)
        );

        DB::beginTransaction();

        try {
            $orderId = DB::table('orders')->insertGetId([
                'ward_id' => 1,
                'req_no' => $reqNo,
                'date' => $orderDate,
                'requested_by' => $actor['id'],
                'ms_approval_status' => 'Pending',
                'approved_by' => null,
                'remark' => json_encode([
                    'category_id' => $this->categoryId,
                    'workflow_status' => 'pending',
                    'requested_by_name' => $actor['name'],
                    'requested_by_id' => $actor['id'],
                    'items' => $items,
                    'created_at' => now()->toIso8601String(),
                ]),
            ]);

            foreach ($items as $index => $item) {
                $medicineId = $this->ensureMedicineRecord(
                    $item['name'],
                    $this->categoryId,
                    $item['unit']
                );

                $detailId = DB::table('order_details')->insertGetId([
                    'order_id' => $orderId,
                    'medicine_id' => $medicineId,
                    'qty_requested' => $item['requested_qty_num'],
                    'qty_issued' => 0,
                    'remark' => $item['unit'],
                ]);

                $items[$index]['id'] = $detailId;
                $items[$index]['medicine_id'] = $medicineId;
            }

            $meta = [
                'category_id' => $this->categoryId,
                'workflow_status' => 'pending',
                'requested_by_name' => $actor['name'],
                'requested_by_id' => $actor['id'],
                'items' => $items,
                'created_at' => now()->toIso8601String(),
            ];

            DB::table('orders')
                ->where('id', $orderId)
                ->update(['remark' => json_encode($meta)]);

            $this->logWorkflowEvent($orderId, 'CREATED', [
                'req_no' => $reqNo,
                'requested_by_id' => $actor['id'],
                'requested_by' => $actor['name'],
                'items' => $items,
            ], $actor['id']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $orderId,
                    'order_id' => $orderId,
                    'req_no' => $reqNo,
                    'date' => $orderDate,
                    'status' => 'pending',
                    'ms_approval_status' => 'Pending',
                    'requested_by' => $actor['name'],
                    'requested_by_id' => $actor['id'],
                    'items' => $items,
                ],
                'message' => "Order #{$reqNo} submitted successfully. Waiting for MS approval.",
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create the requisition: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * STEP 2: MS reviews the nurse request, changes quantities if necessary,
     * and approves it. The actual logged-in MS is saved automatically.
     */
    public function approveRequisition(Request $request, $id)
    {
        $this->verifyRole($request, ['ms', 'admin']);
        $this->ensureLookupData();

        $actor = $this->currentUser($request);

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer',
            'items.*.name' => 'required|string|max:150',
            'items.*.requested_qty_num' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
            'remark' => 'nullable|string|max:1000',
        ]);

        $order = DB::table('orders')->where('id', $id)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Requisition not found.',
            ], 404);
        }

        if ($order->ms_approval_status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'This requisition has already been processed.',
            ], 422);
        }

        $meta = json_decode($order->remark, true) ?: [];
        $existingDetails = DB::table('order_details')
            ->where('order_id', $id)
            ->get()
            ->keyBy('id');

        DB::beginTransaction();

        try {
            $keptDetailIds = [];
            $updatedItems = [];

            foreach ($validated['items'] as $inputItem) {
                $name = strtoupper(trim($inputItem['name']));
                $unit = trim($inputItem['unit'] ?: 'Box');
                $qty = (int) $inputItem['requested_qty_num'];

                if ($qty < 1) {
                    continue;
                }

                $this->ensureGeneralItem($name, $this->categoryId, $unit);
                $medicineId = $this->ensureMedicineRecord($name, $this->categoryId, $unit);

                $detailId = isset($inputItem['id'])
                    ? (int) $inputItem['id']
                    : 0;

                if ($detailId && $existingDetails->has($detailId)) {
                    DB::table('order_details')
                        ->where('id', $detailId)
                        ->where('order_id', $id)
                        ->update([
                            'medicine_id' => $medicineId,
                            'qty_requested' => $qty,
                            'remark' => $unit,
                        ]);
                } else {
                    $detailId = DB::table('order_details')->insertGetId([
                        'order_id' => $id,
                        'medicine_id' => $medicineId,
                        'qty_requested' => $qty,
                        'qty_issued' => 0,
                        'remark' => $unit,
                    ]);
                }

                $keptDetailIds[] = $detailId;

                $oldItem = collect($meta['items'] ?? [])
                    ->first(function ($item) use ($detailId) {
                        return isset($item['id']) && (int) $item['id'] === $detailId;
                    });

                $updatedItems[] = [
                    'id' => $detailId,
                    'medicine_id' => $medicineId,
                    'name' => $name,
                    'requested_qty_num' => $qty,
                    'requested_qty' => $qty . ' ' . $unit,
                    'received_qty_num' => 0,
                    'received_qty' => '',
                    'unit' => $unit,
                    'balance' => $oldItem['balance'] ?? ($this->getCurrentBalance($name) . ' ' . $unit),
                ];
            }

            if (empty($updatedItems)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'At least one item with a quantity of 1 or more is required.',
                ], 422);
            }

            // Any item removed by the MS is removed from order_details.
            if (!empty($keptDetailIds)) {
                DB::table('order_details')
                    ->where('order_id', $id)
                    ->whereNotIn('id', $keptDetailIds)
                    ->delete();
            }

            $meta['workflow_status'] = 'approved';
            $meta['approved_by_id'] = $actor['id'];
            $meta['approved_by_name'] = $actor['name'];
            $meta['approved_at'] = now()->toIso8601String();
            $meta['items'] = $updatedItems;

            if (!empty($validated['remark'])) {
                $meta['approval_remark'] = $validated['remark'];
            }

            DB::table('orders')
                ->where('id', $id)
                ->update([
                    'ms_approval_status' => 'Approved',
                    'approved_by' => $actor['id'],
                    'remark' => json_encode($meta),
                ]);

            $this->logWorkflowEvent($id, 'MS_APPROVED', [
                'approved_by_id' => $actor['id'],
                'approved_by' => $actor['name'],
                'items' => $updatedItems,
            ], $actor['id']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Order #{$order->req_no} approved by {$actor['name']}.",
                'data' => [
                    'id' => $id,
                    'status' => 'approved',
                    'ms_approval_status' => 'Approved',
                    'approved_by' => $actor['name'],
                    'items' => $updatedItems,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to approve the requisition: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Optional MS rejection. A rejected order cannot be deleted by a nurse
     * because the MS has already processed it.
     */
    public function rejectRequisition(Request $request, $id)
    {
        $this->verifyRole($request, ['ms', 'admin']);

        $actor = $this->currentUser($request);

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $order = DB::table('orders')->where('id', $id)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Requisition not found.',
            ], 404);
        }

        if ($order->ms_approval_status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'This requisition has already been processed.',
            ], 422);
        }

        $meta = json_decode($order->remark, true) ?: [];
        $meta['workflow_status'] = 'rejected';
        $meta['rejection_reason'] = $validated['reason'];
        $meta['rejected_by_id'] = $actor['id'];
        $meta['rejected_by_name'] = $actor['name'];
        $meta['rejected_at'] = now()->toIso8601String();

        DB::table('orders')
            ->where('id', $id)
            ->update([
                'ms_approval_status' => 'Rejected',
                'remark' => json_encode($meta),
            ]);

        $this->logWorkflowEvent($id, 'MS_REJECTED', [
            'rejected_by_id' => $actor['id'],
            'rejected_by' => $actor['name'],
            'reason' => $validated['reason'],
        ], $actor['id']);

        return response()->json([
            'success' => true,
            'message' => "Order #{$order->req_no} rejected by {$actor['name']}.",
        ]);
    }

    /**
     * STEP 3: Nurse records the actual quantity received.
     *
     * There is no pharmacist stage in Surgical Consumable 1.
     * order_details.qty_issued is used as the received quantity because the
     * existing database has no qty_received column and the user requested
     * that existing tables must not be changed.
     */
    public function receiveRequisition(Request $request, $id)
    {
        $this->verifyRole($request, ['nurse', 'admin']);
        $this->ensureLookupData();

        $actor = $this->currentUser($request);

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.received_qty_num' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $order = DB::table('orders')
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$order) {
                throw new \RuntimeException('Requisition not found.');
            }

            if ($order->ms_approval_status !== 'Approved') {
                throw new \RuntimeException('The MS must approve this requisition before the nurse can record the received quantity.');
            }

            $meta = json_decode($order->remark, true) ?: [];

            if (($meta['workflow_status'] ?? '') === 'received') {
                throw new \RuntimeException('This requisition has already been received.');
            }

            $updatedItems = [];

            foreach ($validated['items'] as $inputItem) {
                $detail = DB::table('order_details')
                    ->where('id', $inputItem['id'])
                    ->where('order_id', $id)
                    ->lockForUpdate()
                    ->first();

                if (!$detail) {
                    throw new \RuntimeException('One of the order items could not be found.');
                }

                $receivedQty = (int) $inputItem['received_qty_num'];
                $approvedQty = (int) $detail->qty_requested;

                if ($receivedQty > $approvedQty) {
                    throw new \RuntimeException(
                        "Received quantity ({$receivedQty}) cannot exceed the MS-approved quantity ({$approvedQty})."
                    );
                }

                DB::table('order_details')
                    ->where('id', $detail->id)
                    ->update([
                        'qty_issued' => $receivedQty,
                    ]);

                $medicine = DB::table('medicines')
                    ->where('id', $detail->medicine_id)
                    ->first();

                if (!$medicine) {
                    throw new \RuntimeException('Medicine record for an order item could not be found.');
                }

                $unit = $detail->remark ?: 'Box';
                $batchId = $this->ensureMedicineBatch($medicine->id);

                $receiptId = DB::table('stock_receipts')->insertGetId([
                    'batch_id' => $batchId,
                    'ward_id' => 1,
                    'quantity_received' => $receivedQty,
                    'received_by' => $actor['id'],
                    'date' => now(),
                ]);

                // If the database trigger already created the ledger row,
                // do not create a duplicate because source_id is unique.
                $ledgerExists = DB::table('stock_ledger')
                    ->where('source_table', 'stock_receipts')
                    ->where('source_id', $receiptId)
                    ->exists();

                if (!$ledgerExists) {
                    DB::table('stock_ledger')->insert([
                        'batch_id' => $batchId,
                        'ward_id' => 1,
                        'transaction_type' => 'RECEIPT',
                        'quantity' => $receivedQty,
                        'source_table' => 'stock_receipts',
                        'source_id' => $receiptId,
                        'recorded_by' => $actor['id'],
                        'created_at' => now(),
                    ]);
                }

                $generalItemId = $this->ensureGeneralItem(
                    $medicine->name,
                    $this->categoryId,
                    $unit
                );

                DB::table('general_transactions')->insert([
                    'item_id' => $generalItemId,
                    'ward_id' => 1,
                    'quantity_received' => $receivedQty,
                    'quantity_issued' => 0,
                    'date' => now(),
                    'recorded_by' => $actor['id'],
                ]);

                $savedItem = collect($meta['items'] ?? [])
                    ->first(function ($item) use ($detail) {
                        return isset($item['id']) && (int) $item['id'] === (int) $detail->id;
                    });

                $updatedItems[] = [
                    'id' => (int) $detail->id,
                    'medicine_id' => (int) $detail->medicine_id,
                    'name' => $medicine->name,
                    'requested_qty_num' => $approvedQty,
                    'requested_qty' => $approvedQty . ' ' . $unit,
                    'received_qty_num' => $receivedQty,
                    'received_qty' => $receivedQty . ' ' . $unit,
                    'unit' => $unit,
                    'balance' => $savedItem['balance'] ?? ('0 ' . $unit),
                ];
            }

            $meta['workflow_status'] = 'received';
            $meta['received_by_id'] = $actor['id'];
            $meta['received_by_name'] = $actor['name'];
            $meta['received_at'] = now()->toIso8601String();
            $meta['items'] = $updatedItems;

            DB::table('orders')
                ->where('id', $id)
                ->update([
                    'remark' => json_encode($meta),
                ]);

            $this->logWorkflowEvent($id, 'RECEIVED', [
                'received_by_id' => $actor['id'],
                'received_by' => $actor['name'],
                'items' => $updatedItems,
            ], $actor['id']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Order #{$order->req_no} received by {$actor['name']}.",
                'data' => [
                    'id' => $id,
                    'status' => 'received',
                    'received_by' => $actor['name'],
                    'received_by_id' => $actor['id'],
                    'items' => $updatedItems,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Backwards-compatible update endpoint.
     */
    public function updateRequisition(Request $request, $id)
    {
        $action = strtolower((string) $request->input('action', ''));

        if ($action === 'approve') {
            return $this->approveRequisition($request, $id);
        }

        if ($action === 'reject') {
            return $this->rejectRequisition($request, $id);
        }

        if ($action === 'receive') {
            return $this->receiveRequisition($request, $id);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unsupported requisition action.',
        ], 422);
    }

    /**
     * Nurse can delete only their own request while it is still pending.
     * Admin can delete a pending request as well.
     */
    public function destroyRequisition(Request $request, $id)
    {
        $this->verifyRole($request, ['nurse', 'admin']);

        $actor = $this->currentUser($request);
        $activeRole = strtolower((string) $request->attributes->get('active_role', 'nurse'));

        $order = DB::table('orders')
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Requisition not found.',
            ], 404);
        }

        if ($order->ms_approval_status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'This request cannot be deleted because the MS has already processed it.',
            ], 422);
        }

        if ($activeRole !== 'admin' && (int) $order->requested_by !== (int) $actor['id']) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own pending requests.',
            ], 403);
        }

        DB::transaction(function () use ($id, $actor) {
            $this->logWorkflowEvent($id, 'DELETED', [
                'deleted_by_id' => $actor['id'],
                'deleted_by' => $actor['name'],
            ], $actor['id']);

            DB::table('order_details')
                ->where('order_id', $id)
                ->delete();

            DB::table('orders')
                ->where('id', $id)
                ->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Pending requisition deleted successfully.',
        ]);
    }

    /**
     * No pharmacist stage exists for Surgical Consumable 1 anymore.
     * Keep this method only so old code does not cause a fatal error if called.
     */
    public function dispenseRequisition(Request $request, $id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Pharmacist dispensing is not part of the Surgical Consumable 1 workflow. The nurse records the received quantity after MS approval.',
        ], 410);
    }

    /**
     * Get stock adjustments for Surgical Consumable 1 from general_stock_adjustments.
     */
    public function getAdjustments()
    {
        return response()->json(
            WardDatabaseService::getAdjustments($this->categoryId, [])
        );
    }

    /**
     * Store a new stock adjustment for Surgical Consumable 1.
     */
    public function storeAdjustment(Request $request)
    {
        $this->verifyRole($request, ['nurse', 'admin']);

        $actor = $this->currentUser($request);

        $validated = $request->validate([
            'date' => 'required|string',
            'item_name' => 'required|string',
            'type' => 'required|string',
            'quantity' => 'required|string',
            'balance' => 'required|string',
            'reason' => 'required|string',
        ]);

        $amount = 0;
        if (preg_match('/\d+/', $validated['quantity'], $m)) {
            $amount = (int) $m[0];
        }

        $transId = WardDatabaseService::storeAdjustment(
            $this->categoryId,
            $validated['item_name'],
            $validated['type'],
            $amount,
            $validated['reason'],
            $actor['id'],
            $validated['date']
        );

        $validated['id'] = $transId;
        $validated['adjusted_by'] = $actor['name'];

        return response()->json([
            'success' => true,
            'data' => $validated,
            'message' => 'Stock adjustment saved successfully.'
        ], 201);
    }

    /**
     * Delete a stock adjustment record.
     */
    public function destroyAdjustment($id)
    {
        WardDatabaseService::deleteAdjustment($id);

        return response()->json([
            'success' => true,
            'message' => 'Stock adjustment record removed.'
        ]);
    }

    /**
     * Get daily counts for Surgical Consumable 1.
     */
    public function getDailyCounts()
    {
        $fallback = [];

        return response()->json(
            WardDatabaseService::getDailyCounts($this->categoryId, $fallback)
        );
    }

    /**
     * Store a Daily Count and reduce the selected item's available stock.
     */
    public function storeDailyCount(Request $request)
    {
        $this->verifyRole($request, ['nurse', 'admin']);

        $actor = $this->currentUser($request);

        $validated = $request->validate([
            'date' => 'required|date',
            'item_name' => 'required|string|max:150',
            'qty_given' => 'required|integer|min:1',
            'sisters_initials' => 'nullable|string|max:20',
            'remarks' => 'nullable|string|max:1000',
        ]);

        try {
            $initials = $validated['sisters_initials']
                ?: collect(preg_split('/\s+/', trim($actor['name'])))
                    ->filter()
                    ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                    ->implode('');

            $result = WardDatabaseService::storeDailyCount(
                $this->categoryId,
                $validated['item_name'],
                (int) $validated['qty_given'],
                $initials,
                $validated['remarks'] ?? '',
                $validated['date'],
                $actor['id']
            );

            return response()->json([
                'success' => true,
                'message' => 'Daily count recorded successfully and stock balance reduced.',
                'data' => [
                    'id' => $result['id'],
                    'date' => $validated['date'],
                    'item_name' => $validated['item_name'],
                    'qty_given' => (int) $validated['qty_given'],
                    'balance' => $result['balance'],
                    'sisters_initials' => strtoupper($initials),
                    'remarks' => $validated['remarks'] ?? '',
                ],
            ], 201);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}