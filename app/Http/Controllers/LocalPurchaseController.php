<?php

namespace App\Http\Controllers;

use App\Services\WardDatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocalPurchaseController extends Controller
{
    private const MODULE = 'local_purchase';
    private const CATEGORY_ID = 3;

    private array $defaultItems = [
        ['name' => 'PARACETAMOL SYRUP', 'balance' => 18, 'unit' => 'Bottle'],
        ['name' => 'NORMAL SALINE 100ML', 'balance' => 25, 'unit' => 'Bottle'],
        ['name' => 'GLUCOMETER STRIP', 'balance' => 8, 'unit' => 'Box'],
        ['name' => 'ALCOHOL SWAB', 'balance' => 40, 'unit' => 'Box'],
        ['name' => 'ADHESIVE TAPE', 'balance' => 12, 'unit' => 'Roll'],
        ['name' => 'DISINFECTANT SOLUTION', 'balance' => 20, 'unit' => 'Bottle'],
        ['name' => 'FACE MASK', 'balance' => 50, 'unit' => 'Box'],
        ['name' => 'HAND SANITIZER', 'balance' => 35, 'unit' => 'Bottle'],
        ['name' => 'TISSUE PACK', 'balance' => 6, 'unit' => 'Pack'],
        ['name' => 'WASTE BAG', 'balance' => 60, 'unit' => 'Pack'],
    ];

    /**
     * Display Local Purchases page.
     */
    public function index(Request $request, $role = null)
    {
        $activeRole = $request->attributes->get('active_role');

        if (!$activeRole) {
            abort(403, 'Authenticated user role could not be determined.');
        }

        $activeRole = strtolower(trim((string) $activeRole));

        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $currentUserId = (int) $user->id;
        $currentUserName = (string) $user->name;

        return view('local-purchases', [
            'activeRole' => $activeRole,
            'currentUserId' => $currentUserId,
            'currentUserName' => $currentUserName,
        ]);
    }

    /**
     * Get Local Purchase items.
     */
    public function getItems()
{
    $items = DB::table('general_items')
        ->where('category_id', self::CATEGORY_ID)
        ->orderBy('name')
        ->get()
        ->map(function ($item) {
            return [
                'id' => (int) $item->id,
                'name' => $item->name,
                'balance' => (int) ($item->balance ?? 0),
                'unit' => $item->unit ?? 'Box',
                'category_id' => (int) ($item->category_id ?? 0),
            ];
        });

    if ($items->isEmpty()) {
        $items = collect($this->defaultItems)->map(function ($item, $index) {
            return [
                'id' => $index + 1,
                'name' => $item['name'],
                'balance' => (int) ($item['balance'] ?? 0),
                'unit' => $item['unit'] ?? 'Box',
                'category_id' => self::CATEGORY_ID,
            ];
        });
    }

    return response()->json($items->values());
}

    /**
     * Add Local Purchase item.
     */
    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'balance' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
        ]);

        $item = WardDatabaseService::storeItem(
            self::CATEGORY_ID,
            $validated['name'],
            $validated['balance'],
            $validated['unit']
        );

        return response()->json([
            'success' => true,
            'data' => $item,
            'message' => 'Local purchase item added successfully.',
        ], 201);
    }

    /**
     * Update Local Purchase item.
     */
    public function updateItem(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
        ]);

        WardDatabaseService::updateItem(
            $id,
            $validated['name']
        );

        return response()->json([
            'success' => true,
            'message' => 'Local purchase item updated successfully.',
        ]);
    }

    /**
     * Delete Local Purchase item.
     */
    public function destroyItem(Request $request, $id)
    {
        $this->verifyRole($request, ['nurse', 'admin']);

        WardDatabaseService::deleteItem($id);

        return response()->json([
            'success' => true,
            'message' => 'Local purchase item removed successfully.',
        ]);
    }

    /**
     * Get the currently logged-in user.
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
     * Ensure general item exists.
     */
    private function ensureGeneralItem(
        string $name,
        int $categoryId,
        string $unitName = 'Box'
    )
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
            'item_code' => sprintf(
                'CAT%d-%d',
                $categoryId,
                time()
            ),
            'name' => $name,
            'category_id' => $categoryId,
        ]);
    }

    /**
     * Ensure medicine record exists.
     */
    private function ensureMedicineRecord(
        string $name,
        int $categoryId,
        string $unitName = 'Box'
    )
    {
        $name = strtoupper(trim($name));

        $unit = DB::table('units')
            ->where('unit_name', $unitName)
            ->first();

        $unitId = $unit?->id ?? 1;

        $medicine = DB::table('medicines')
            ->where('name', $name)
            ->first();

        if ($medicine) {
            return $medicine->id;
        }

        return DB::table('medicines')->insertGetId([
            'item_code' => sprintf(
                'MED-%d-%d-%03d',
                $categoryId,
                time(),
                mt_rand(10, 99)
            ),
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
     * Get current balance.
     */
    private function currentBalanceForItem(string $itemName): int
    {
        $item = DB::table('general_items')
            ->where('category_id', self::CATEGORY_ID)
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
     * Log workflow event.
     */
    private function logWorkflowEvent(
        $transactionId,
        $event,
        array $data,
        ?int $userId = null
    ) {
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
            'action_performed' =>
                "LPWF|{$transactionId}|{$event}|" .
                json_encode($data),
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);
    }

    /**
     * Load Local Purchase workflow events.
     */
    private function loadWorkflowEvents(): array
    {
        $logs = DB::table('activity_logs')
            ->where('action_performed', 'like', 'LPWF|%')
            ->orderBy('id')
            ->get();

        $events = [];

        foreach ($logs as $log) {
            $parts = explode(
                '|',
                $log->action_performed,
                4
            );

            if (count($parts) !== 4) {
                continue;
            }

            $transactionId = (int) $parts[1];
            $event = $parts[2];
            $data = json_decode(
                $parts[3],
                true
            ) ?: [];

            $events[$transactionId][$event] = $data;
        }

        return $events;
    }

    /**
     * Get Local Purchase requisitions.
     */
    public function getRequisitions()
    {
        $workflowEvents = $this->loadWorkflowEvents();

        $orders = DB::table('orders')
            ->leftJoin(
                'users as requester',
                'orders.requested_by',
                '=',
                'requester.id'
            )
            ->leftJoin(
                'users as approver',
                'orders.approved_by',
                '=',
                'approver.id'
            )
            ->where('orders.ward_id', 1)
            ->orderByDesc('orders.id')
            ->select(
                'orders.*',
                DB::raw("
                    NULLIF(
                        TRIM(
                            CONCAT(
                                COALESCE(requester.first_name, ''),
                                ' ',
                                COALESCE(requester.last_name, '')
                            )
                        ),
                        ''
                    ) as requester_name
                "),
                DB::raw("
                    NULLIF(
                        TRIM(
                            CONCAT(
                                COALESCE(approver.first_name, ''),
                                ' ',
                                COALESCE(approver.last_name, '')
                            )
                        ),
                        ''
                    ) as approver_name
                ")
            )
            ->get();

        $results = [];

       foreach ($orders as $order) {
        $meta = json_decode(
            $order->remark,
            true
        ) ?: [];

        // Local Purchase records are identified by module.
        // Keep category_id only as a fallback for older records.
        $module = $meta['module'] ?? null;

        if ($module !== null) {
            if ($module !== self::MODULE) {
                continue;
            }
        } else {
            $categoryId = (int) ($meta['category_id'] ?? self::CATEGORY_ID);

            if ($categoryId !== (int) self::CATEGORY_ID) {
                continue;
            }
        }


            $details = DB::table('order_details')
                ->leftJoin(
                    'medicines',
                    'order_details.medicine_id',
                    '=',
                    'medicines.id'
                )
                ->where(
                    'order_details.order_id',
                    $order->id
                )
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

                $requestedQty =
                    (int) $detail->qty_requested;

               $receivedQty =
                    (int) ($detail->qty_issued ?? 0);

                $balanceQty = $this->currentBalanceForItem(
                    $detail->medicine_name
                );

                $items[] = [
                    'id' => (int) $detail->id,
                    'medicine_id' =>
                        (int) $detail->medicine_id,
                    'name' =>
                        $detail->medicine_name
                        ?: 'LOCAL PURCHASE ITEM',
                    'requested_qty_num' =>
                        $requestedQty,
                    'requested_qty' =>
                        $requestedQty . ' ' . $unit,
                    'received_qty_num' =>
                        $receivedQty,
                   'received_qty' =>
                        $receivedQty > 0
                            ? $receivedQty . ' ' . $unit
                            : '',

                    'balance_num' => $balanceQty,

                    'balance' => $balanceQty . ' ' . $unit,

                    'unit' => $unit,
                ];
            }
            $firstItem = $items[0] ?? null;
            $status = strtolower(
                $meta['workflow_status']
                ?? $order->ms_approval_status
                ?? 'pending'
            );

            if ($order->ms_approval_status === 'Rejected') {
                $status = 'rejected';
            }

            if (
                $order->ms_approval_status === 'Approved'
                && $status === 'pending'
            ) {
                $status = 'approved';
            }

            if (
                ($meta['workflow_status'] ?? '') === 'received'
            ) {
                $status = 'received';
            }

           $results[] = [
                'id' => (int) $order->id,
                'order_id' => (int) $order->id,
                'req_no' => $order->req_no,

                'date' => date(
                    'Y-m-d H:i:s',
                    strtotime($order->date)
                ),

                'status' => $status,

                'ms_approval_status' =>
                    $order->ms_approval_status,

                'supply_name' =>
                    $firstItem['name'] ?? 'LOCAL PURCHASE ITEM',

                'balance' =>
                    $firstItem['balance'] ?? '0 Box',

                'requested_qty' =>
                    $firstItem['requested_qty'] ?? '0 Box',

                'received_qty' =>
                    $firstItem['received_qty'] ?? '',

                'requested_by' =>
                    $order->requester_name
                    ?? $meta['requested_by_name']
                    ?? '-',

                'requested_by_id' =>
                    (int) $order->requested_by,

                // keep the rest of your existing fields...
            ];
        }

        return response()->json($results);
    }

    /**
     * Nurse creates a Local Purchase request.
     */
    public function storeRequisition(Request $request)
    {
        $this->verifyRole(
            $request,
            ['nurse', 'admin']
        );

        $actor = $this->currentUser($request);

        $validated = $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.name' =>
                'required|string|max:150',
            'items.*.requested_qty_num' =>
                'required|integer|min:1',
            'items.*.unit' =>
                'required|string|max:50',
        ]);

        $orderDate = date(
            'Y-m-d H:i:s',
            strtotime($validated['date'])
        );

        $items = [];

        foreach ($validated['items'] as $inputItem) {
            $name = strtoupper(
                trim($inputItem['name'])
            );

            $unit = trim(
                $inputItem['unit'] ?: 'Box'
            );

            $qty = (int)
                $inputItem['requested_qty_num'];

            $this->ensureGeneralItem(
                $name,
                self::CATEGORY_ID,
                $unit
            );

            $items[] = [
                'name' => $name,
                'requested_qty_num' => $qty,
                'requested_qty' =>
                    $qty . ' ' . $unit,
                'received_qty_num' => 0,
                'received_qty' => '',
                'unit' => $unit,
            ];
        }

        $reqNo = sprintf(
            'LP-%s-%04d',
            date('YmdHis'),
            random_int(1000, 9999)
        );

        DB::beginTransaction();

        try {
            $orderId = DB::table('orders')
                ->insertGetId([
                    'ward_id' => 1,
                    'req_no' => $reqNo,
                    'date' => $orderDate,
                    'requested_by' => $actor['id'],
                    'ms_approval_status' =>
                        'Pending',
                    'approved_by' => null,
                    'remark' => json_encode([
                        'module' => self::MODULE,
                        'category_id' => self::CATEGORY_ID,
                        'workflow_status' => 'pending',
                        'requested_by_name' => $actor['name'],
                        'requested_by_id' => $actor['id'],
                        'items' => $items,
                        'created_at' => now()->toIso8601String(),
                    ]),
                ]);

            foreach ($items as $index => $item) {
                $medicineId =
                    $this->ensureMedicineRecord(
                        $item['name'],
                        self::CATEGORY_ID,
                        $item['unit']
                    );

                $detailId =
                    DB::table('order_details')
                        ->insertGetId([
                            'order_id' => $orderId,
                            'medicine_id' =>
                                $medicineId,
                            'qty_requested' =>
                                $item[
                                    'requested_qty_num'
                                ],
                            'qty_issued' => 0,
                            'remark' =>
                                $item['unit'],
                        ]);

                $items[$index]['id'] =
                    $detailId;

                $items[$index]['medicine_id'] =
                    $medicineId;
            }

            DB::table('orders')
                ->where('id', $orderId)
                ->update([
                   'remark' => json_encode([
                        'module' => self::MODULE,
                        'category_id' => self::CATEGORY_ID,
                        'workflow_status' => 'pending',
                        'requested_by_name' => $actor['name'],
                        'requested_by_id' => $actor['id'],
                        'items' => $items,
                        'created_at' => now()->toIso8601String(),
                    ]),
                ]);

            $this->logWorkflowEvent(
                $orderId,
                'CREATED',
                [
                    'req_no' => $reqNo,
                    'requested_by_id' =>
                        $actor['id'],
                    'requested_by' =>
                        $actor['name'],
                    'items' => $items,
                ],
                $actor['id']
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $orderId,
                    'order_id' => $orderId,
                    'req_no' => $reqNo,
                    'date' => $orderDate,
                    'status' => 'pending',
                    'ms_approval_status' =>
                        'Pending',
                    'requested_by' =>
                        $actor['name'],
                    'requested_by_id' =>
                        $actor['id'],
                    'items' => $items,
                ],
                'message' =>
                    "Order #{$reqNo} submitted successfully. Waiting for MS approval.",
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Unable to create the requisition: '
                    . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * MS approves a Local Purchase request.
     */
    public function approveRequisition(
        Request $request,
        $id
    ) {
        $this->verifyRole(
            $request,
            ['ms', 'admin']
        );

        $actor = $this->currentUser($request);

        $validated = $request->validate([
            'items' =>
                'required|array|min:1',
            'items.*.id' =>
                'nullable|integer',
            'items.*.name' =>
                'required|string|max:150',
            'items.*.requested_qty_num' =>
                'required|integer|min:1',
            'items.*.unit' =>
                'required|string|max:50',
            'remark' =>
                'nullable|string|max:1000',
        ]);

        $order = DB::table('orders')
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Requisition not found.',
            ], 404);
        }

        if (
            $order->ms_approval_status
            !== 'Pending'
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'This requisition has already been processed.',
            ], 422);
        }

        $meta = json_decode(
            $order->remark,
            true
        ) ?: [];

        DB::beginTransaction();

        try {
            $updatedItems = [];

            foreach ($validated['items'] as $inputItem) {
                $name = strtoupper(
                    trim($inputItem['name'])
                );

                $unit = trim(
                    $inputItem['unit'] ?: 'Box'
                );

                $qty = (int)
                    $inputItem['requested_qty_num'];

                $this->ensureGeneralItem(
                    $name,
                    self::CATEGORY_ID,
                    $unit
                );

                $medicineId =
                    $this->ensureMedicineRecord(
                        $name,
                        self::CATEGORY_ID,
                        $unit
                    );

                $detailId =
                    isset($inputItem['id'])
                        ? (int) $inputItem['id']
                        : 0;

                if ($detailId) {
                    DB::table('order_details')
                        ->where('id', $detailId)
                        ->where(
                            'order_id',
                            $id
                        )
                        ->update([
                            'medicine_id' =>
                                $medicineId,
                            'qty_requested' =>
                                $qty,
                            'remark' =>
                                $unit,
                        ]);
                } else {
                    $detailId =
                        DB::table(
                            'order_details'
                        )->insertGetId([
                            'order_id' => $id,
                            'medicine_id' =>
                                $medicineId,
                            'qty_requested' =>
                                $qty,
                            'qty_issued' => 0,
                            'remark' =>
                                $unit,
                        ]);
                }

                $updatedItems[] = [
                    'id' => $detailId,
                    'medicine_id' =>
                        $medicineId,
                    'name' => $name,
                    'requested_qty_num' =>
                        $qty,
                    'requested_qty' =>
                        $qty . ' ' . $unit,
                    'approved_quantity' =>
                        $qty,
                    'received_qty_num' => 0,
                    'received_qty' => '',
                    'unit' => $unit,
                ];
            }

            $meta['workflow_status'] =
                'approved';

            $meta['approved_by_id'] =
                $actor['id'];

            $meta['approved_by_name'] =
                $actor['name'];

            $meta['approved_at'] =
                now()->toIso8601String();

            $meta['items'] =
                $updatedItems;

            if (!empty($validated['remark'])) {
                $meta['approval_remark'] =
                    $validated['remark'];
            }

            DB::table('orders')
                ->where('id', $id)
                ->update([
                    'ms_approval_status' =>
                        'Approved',
                    'approved_by' =>
                        $actor['id'],
                    'remark' =>
                        json_encode($meta),
                ]);

            $this->logWorkflowEvent(
                $id,
                'MS_APPROVED',
                [
                    'approved_by_id' =>
                        $actor['id'],
                    'approved_by' =>
                        $actor['name'],
                    'items' =>
                        $updatedItems,
                ],
                $actor['id']
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' =>
                    "Order #{$order->req_no} approved by {$actor['name']}.",
                'data' => [
                    'id' => $id,
                    'status' => 'approved',
                    'ms_approval_status' =>
                        'Approved',
                    'approved_by' =>
                        $actor['name'],
                    'items' =>
                        $updatedItems,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Unable to approve the requisition: '
                    . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * MS rejects a Local Purchase request.
     */
    public function rejectRequisition(
        Request $request,
        $id
    ) {
        $this->verifyRole(
            $request,
            ['ms', 'admin']
        );

        $actor = $this->currentUser($request);

        $validated = $request->validate([
            'reason' =>
                'required|string|max:1000',
        ]);

        $order = DB::table('orders')
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Requisition not found.',
            ], 404);
        }

        if (
            $order->ms_approval_status
            !== 'Pending'
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'This requisition has already been processed.',
            ], 422);
        }

        $meta = json_decode(
            $order->remark,
            true
        ) ?: [];

        $meta['workflow_status'] =
            'rejected';

        $meta['rejection_reason'] =
            $validated['reason'];

        $meta['rejected_by_id'] =
            $actor['id'];

        $meta['rejected_by_name'] =
            $actor['name'];

        $meta['rejected_at'] =
            now()->toIso8601String();

        DB::table('orders')
            ->where('id', $id)
            ->update([
                'ms_approval_status' =>
                    'Rejected',
                'remark' =>
                    json_encode($meta),
            ]);

        $this->logWorkflowEvent(
            $id,
            'MS_REJECTED',
            [
                'rejected_by_id' =>
                    $actor['id'],
                'rejected_by' =>
                    $actor['name'],
                'reason' =>
                    $validated['reason'],
            ],
            $actor['id']
        );

        return response()->json([
            'success' => true,
            'message' =>
                "Order #{$order->req_no} rejected by {$actor['name']}.",
        ]);
    }

    /**
     * Nurse receives approved Local Purchase.
     */
    public function receiveRequisition(
        Request $request,
        $id
    ) {
        $this->verifyRole(
            $request,
            ['nurse', 'admin']
        );

        $actor = $this->currentUser($request);

        $validated = $request->validate([
            'items' =>
                'required|array|min:1',
            'items.*.id' =>
                'required|integer',
            'items.*.received_qty_num' =>
                'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $order = DB::table('orders')
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$order) {
                throw new \RuntimeException(
                    'Requisition not found.'
                );
            }

            if (
                $order->ms_approval_status
                !== 'Approved'
            ) {
                throw new \RuntimeException(
                    'The MS must approve this requisition before the nurse can receive it.'
                );
            }

            $meta = json_decode(
                $order->remark,
                true
            ) ?: [];

            if (
                ($meta['workflow_status'] ?? '')
                === 'received'
            ) {
                throw new \RuntimeException(
                    'This requisition has already been received.'
                );
            }

            $updatedItems = [];

            foreach ($validated['items'] as $inputItem) {
                $detail = DB::table('order_details')
                    ->where(
                        'id',
                        $inputItem['id']
                    )
                    ->where(
                        'order_id',
                        $id
                    )
                    ->lockForUpdate()
                    ->first();

                if (!$detail) {
                    throw new \RuntimeException(
                        'One of the order items could not be found.'
                    );
                }

                $receivedQty =
                    (int) $inputItem[
                        'received_qty_num'
                    ];

                $approvedQty =
                    (int) $detail->qty_requested;

                if (
                    $receivedQty >
                    $approvedQty
                ) {
                    throw new \RuntimeException(
                        "Received quantity ({$receivedQty}) cannot exceed the approved quantity ({$approvedQty})."
                    );
                }

                DB::table('order_details')
                    ->where(
                        'id',
                        $detail->id
                    )
                    ->update([
                        'qty_issued' =>
                            $receivedQty,
                    ]);

                $medicine = DB::table('medicines')
                    ->where(
                        'id',
                        $detail->medicine_id
                    )
                    ->first();

                if (!$medicine) {
                    throw new \RuntimeException(
                        'Medicine record could not be found.'
                    );
                }

                $unit =
                    $detail->remark ?: 'Box';

                $generalItemId =
                    $this->ensureGeneralItem(
                        $medicine->name,
                        self::CATEGORY_ID,
                        $unit
                    );

                DB::table(
                    'general_transactions'
                )->insert([
                    'item_id' =>
                        $generalItemId,
                    'ward_id' => 1,
                    'quantity_received' =>
                        $receivedQty,
                    'quantity_issued' => 0,
                    'date' => now(),
                    'recorded_by' =>
                        $actor['id'],
                ]);

                $updatedItems[] = [
                    'id' =>
                        (int) $detail->id,
                    'medicine_id' =>
                        (int) $detail->medicine_id,
                    'name' =>
                        $medicine->name,
                    'requested_qty_num' =>
                        $approvedQty,
                    'requested_qty' =>
                        $approvedQty . ' ' . $unit,
                    'approved_quantity' =>
                        $approvedQty,
                    'received_qty_num' =>
                        $receivedQty,
                    'received_qty' =>
                        $receivedQty . ' ' . $unit,
                    'unit' => $unit,
                ];
            }

            $meta['workflow_status'] =
                'received';

            $meta['received_by_id'] =
                $actor['id'];

            $meta['received_by_name'] =
                $actor['name'];

            $meta['received_at'] =
                now()->toIso8601String();

            $meta['items'] =
                $updatedItems;

            DB::table('orders')
                ->where('id', $id)
                ->update([
                    'remark' =>
                        json_encode($meta),
                ]);

            $this->logWorkflowEvent(
                $id,
                'RECEIVED',
                [
                    'received_by_id' =>
                        $actor['id'],
                    'received_by' =>
                        $actor['name'],
                    'items' =>
                        $updatedItems,
                ],
                $actor['id']
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' =>
                    "Order #{$order->req_no} received by {$actor['name']}.",
                'data' => [
                    'id' => $id,
                    'status' => 'received',
                    'received_by' =>
                        $actor['name'],
                    'received_by_id' =>
                        $actor['id'],
                    'items' =>
                        $updatedItems,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Backwards-compatible requisition action endpoint.
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
     * Delete pending Local Purchase request.
     */
    public function destroyRequisition(
        Request $request,
        $id
    ) {
        $this->verifyRole(
            $request,
            ['nurse', 'admin']
        );

        $actor = $this->currentUser($request);

        $activeRole = strtolower(
            (string) $request
                ->attributes
                ->get(
                    'active_role',
                    'nurse'
                )
        );

        $order = DB::table('orders')
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Requisition not found.',
            ], 404);
        }

        if (
            $order->ms_approval_status
            !== 'Pending'
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'This request cannot be deleted because the MS has already processed it.',
            ], 422);
        }

        if (
            $activeRole !== 'admin'
            && (int) $order->requested_by
                !== (int) $actor['id']
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'You can only delete your own pending requests.',
            ], 403);
        }

        DB::transaction(
            function () use ($id, $actor) {
                $this->logWorkflowEvent(
                    $id,
                    'DELETED',
                    [
                        'deleted_by_id' =>
                            $actor['id'],
                        'deleted_by' =>
                            $actor['name'],
                    ],
                    $actor['id']
                );

                DB::table('order_details')
                    ->where(
                        'order_id',
                        $id
                    )
                    ->delete();

                DB::table('orders')
                    ->where('id', $id)
                    ->delete();
            }
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Pending requisition deleted successfully.',
        ]);
    }

    /**
     * Get Local Purchase adjustments.
     */
    public function getAdjustments()
    {
        return response()->json(
            WardDatabaseService::getAdjustments(
                self::CATEGORY_ID,
                []
            )
        );
    }

    /**
     * Store Local Purchase adjustment.
     */
    public function storeAdjustment(
        Request $request
    ) {
        $this->verifyRole($request, ['nurse', 'admin']);

        $actor = $this->currentUser($request);

        $validated = $request->validate([
            'date' =>
                'required|date',
            'item_name' =>
                'required|string|max:150',
            'type' =>
                'required|in:increase,decrease',
            'quantity' =>
                'required|integer|min:1',
            'reason' =>
                'required|string|max:500',
        ]);

        $amount = (int) $validated['quantity'];

        try {
            $transId =
                WardDatabaseService::storeAdjustment(
                    self::CATEGORY_ID,
                    $validated['item_name'],
                    $validated['type'],
                    $amount,
                    $validated['reason'],
                    $actor['id'],
                    $validated['date']
                );
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $validated['id'] = $transId;
        $validated['adjusted_by'] = $actor['name'];

        return response()->json([
            'success' => true,
            'data' => $validated,
            'message' =>
                'Stock adjustment saved successfully.',
        ], 201);
    }

    /**
     * Delete Local Purchase adjustment.
     */
    public function destroyAdjustment(Request $request, $id)
    {
        $this->verifyRole($request, ['nurse', 'admin']);

        WardDatabaseService::deleteAdjustment($id);

        return response()->json([
            'success' => true,
            'message' => 'Stock adjustment record removed.'
        ]);
    }

    /**
     * Get daily counts.
     */
    public function getDailyCounts()
    {
        $fallback = [];

        return response()->json(
            WardDatabaseService::getDailyCounts(self::CATEGORY_ID, $fallback)
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
            'sisters_initials' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:1000',
        ]);

        try {
            $sistersName = trim($actor['name']);

            $result = WardDatabaseService::storeDailyCount(
                self::CATEGORY_ID,
                $validated['item_name'],
                (int) $validated['qty_given'],
                $sistersName,
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
                    'sisters_initials' => $sistersName,
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