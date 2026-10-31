<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WardDatabaseService
{
    /**
     * Verify that the required master records already exist.
     *
     * IMPORTANT:
     * This method is READ-ONLY.
     * It never inserts, updates, or deletes anything.
     */
    public static function ensureMasterRecords(): void
    {
        $roleExists = DB::table('roles')
            ->where('id', 1)
            ->exists();

        $wardExists = DB::table('wards')
            ->where('id', 1)
            ->exists();

        $categoriesExist = DB::table('categories')
            ->whereIn('id', [1, 2, 3])
            ->count() === 3;

        if (!$roleExists) {
            throw new \RuntimeException(
                'Required role record with ID 1 does not exist.'
            );
        }

        if (!$wardExists) {
            throw new \RuntimeException(
                'Required ward record with ID 1 does not exist.'
            );
        }

        if (!$categoriesExist) {
            throw new \RuntimeException(
                'Required inventory category records do not exist.'
            );
        }
    }

    /**
     * Get the existing Ward 48 ID.
     *
     * READ-ONLY: this method never creates or changes anything.
     */
    public static function getWardId(): int
    {
        $ward = DB::table('wards')
            ->where('ward_number', 'W-48')
            ->first();

        if (!$ward) {
            throw new \RuntimeException(
                'Ward W-48 was not found in the database.'
            );
        }

        return (int) $ward->id;
    }

    /**
     * Return the currently authenticated user's ID.
     *
     * Throws an exception when no user is authenticated.
     */
    private static function currentUserId(): int
    {
        if (auth()->check()) {
            return (int) auth()->id();
        }

        throw new \RuntimeException(
            'No authenticated user found.'
        );
    }

    /**
     * Convert date input safely.
     */
    private static function normalizeDate($dateStr)
    {
        if (empty($dateStr)) {
            return now();
        }

        try {
            $timestamp = strtotime($dateStr);

            if ($timestamp === false) {
                return now();
            }

            return date('Y-m-d H:i:s', $timestamp);
        } catch (\Throwable $e) {
            return now();
        }
    }

    /**
     * Get all items for a category with calculated balances.
     *
     * IMPORTANT:
     * This method is READ-ONLY.
     *
     * It only returns items that already exist in
     * the general_items table.
     *
     * It never creates sample/fallback inventory items.
     *
     * Balance:
     *
     *     Total Received - Total Issued
     */
    public static function getCategoryItems(
        $categoryId,
        array $defaultFallback = []
    ) {
        self::ensureMasterRecords();

        try {
            $items = DB::table('general_items')
                ->where('category_id', $categoryId)
                ->get();

            if ($items->count() > 0) {
                $result = [];

                foreach ($items as $item) {
                    $received = (int) DB::table(
                        'general_transactions'
                    )
                        ->where('item_id', $item->id)
                        ->sum('quantity_received');

                    $issued = (int) DB::table(
                        'general_transactions'
                    )
                        ->where('item_id', $item->id)
                        ->sum('quantity_issued');

                    /*
                     * Receiving increases stock.
                     * Daily Count / issue decreases stock.
                     */
                    $balance = max(
                        0,
                        $received - $issued
                    );

                    $result[] = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'balance' => $balance,
                        'unit' => self::guessUnitFromName(
                            $item->name
                        ),
                    ];
                }

                return $result;
            }
        } catch (\Exception $e) {
            /*
             * Do not create fallback records.
             */
            return [];
        }

        /*
         * No database records means an empty list.
         */
        return [];
    }

    /**
     * Add a new item.
     *
     * This method is intentionally available only when
     * the application explicitly creates an item.
     */
    public static function storeItem(
        $categoryId,
        $name,
        $balance,
        $unit
    ) {
        self::ensureMasterRecords();

        $name = strtoupper(trim($name));

        $itemCode = sprintf(
            'CAT%d-%d-%04d',
            $categoryId,
            time(),
            mt_rand(100, 999)
        );

        $userId = self::currentUserId();

        try {
            $itemId = DB::table('general_items')
                ->insertGetId([
                    'item_code' => $itemCode,
                    'name' => $name,
                    'category_id' => $categoryId,
                ]);

            /*
             * Initial balance is treated as received stock.
             */
            if ((int) $balance > 0) {
                DB::table('general_transactions')
                    ->insert([
                        'item_id' => $itemId,
                        'ward_id' => WardDatabaseService::getWardId(),
                        'quantity_received' =>
                            (int) $balance,
                        'quantity_issued' => 0,
                        'date' => now(),
                        'recorded_by' => $userId,
                    ]);
            }

            self::logActivity(
                'Added item: ' . $name,
                $userId
            );

            return [
                'id' => $itemId,
                'name' => $name,
                'balance' => (int) $balance,
                'unit' => $unit,
            ];
        } catch (\Exception $e) {
            return [
                'id' => time(),
                'name' => $name,
                'balance' => (int) $balance,
                'unit' => $unit,
            ];
        }
    }

    /**
     * Update item name.
     */
    public static function updateItem($itemId, $name)
    {
        self::ensureMasterRecords();

        $name = strtoupper(trim($name));

        DB::table('general_items')
            ->where('id', $itemId)
            ->update([
                'name' => $name,
            ]);

        self::logActivity(
            'Updated item #' . $itemId .
            ' name to ' . $name
        );

        return true;
    }

    /**
     * Delete item.
     */
    public static function deleteItem($itemId)
    {
        self::ensureMasterRecords();

        DB::table('general_transactions')
            ->where('item_id', $itemId)
            ->delete();

        DB::table('general_items')
            ->where('id', $itemId)
            ->delete();

        self::logActivity(
            'Deleted item #' . $itemId
        );

        return true;
    }

    /**
     * Add a transaction.
     *
     * $receivedQty = stock coming IN.
     * $issuedQty   = stock going OUT.
     *
     * IMPORTANT:
     * The item MUST already exist in general_items.
     * This method never creates an inventory item automatically.
     */
    public static function storeTransaction(
        $categoryId,
        $itemName,
        $receivedQty,
        $issuedQty,
        $dateStr,
        $note = '',
        $recordedBy = null
    ) {
        self::ensureMasterRecords();

        $recordedBy = $recordedBy !== null
            ? (int) $recordedBy
            : self::currentUserId();

        $itemName = strtoupper(trim($itemName));

        /*
         * Find the existing item in the selected category.
         */
        $item = DB::table('general_items')
            ->where('name', $itemName)
            ->where('category_id', $categoryId)
            ->first();

        /*
         * Never create an item automatically.
         */
        if (!$item) {
            throw new \RuntimeException(
                'The inventory item could not be found in the database.'
            );
        }

        $itemId = $item->id;

        $transactionData = [
            'item_id' => $itemId,
            'ward_id' => WardDatabaseService::getWardId(),

            /*
             * Received quantity INCREASES stock.
             */
            'quantity_received' =>
                max(0, (int) $receivedQty),

            /*
             * Issued quantity DECREASES stock.
             */
            'quantity_issued' =>
                max(0, (int) $issuedQty),

            'date' => self::normalizeDate($dateStr),
            'recorded_by' => $recordedBy,
        ];

        /*
         * Store optional Daily Count information if
         * those columns already exist.
         */
        try {
            if (
                Schema::hasColumn(
                    'general_transactions',
                    'sisters_initials'
                )
            ) {
                // storeDailyCount() handles this.
            }

            if (
                Schema::hasColumn(
                    'general_transactions',
                    'remarks'
                )
            ) {
                // storeDailyCount() handles this.
            }
        } catch (\Throwable $e) {
            // Ignore schema inspection failure.
        }

        $transId = DB::table(
            'general_transactions'
        )->insertGetId($transactionData);

        self::logActivity(
            'Recorded transaction for item: '
            . $itemName
            . ' (Received: '
            . (int) $receivedQty
            . ', Issued: '
            . (int) $issuedQty
            . ')'
            . ($note ? ' | ' . $note : ''),
            $recordedBy
        );

        return $transId;
    }

    /**
     * Log activity.
     */
    public static function logActivity(
        $action,
        $userId = null
    ) {
        try {
            $userId = $userId !== null
                ? (int) $userId
                : self::currentUserId();

            DB::table('activity_logs')
                ->insert([
                    'user_id' => $userId,
                    'action_performed' => $action,
                    'ip_address' =>
                        function_exists('request')
                            ? (
                                request()->ip()
                                ?? '127.0.0.1'
                            )
                            : '127.0.0.1',
                    'created_at' => now(),
                ]);
        } catch (\Exception $e) {
            // Activity logging must never break
            // the main operation.
        }
    }

    /**
     * Get stock adjustments for a category.
     *
     * READ-ONLY.
     *
     * Only records already stored in
     * general_stock_adjustments are returned.
     */
    public static function getAdjustments(
        $categoryId
    ) {
        try {
            $adjustments = DB::table(
                'general_stock_adjustments'
            )
                ->join(
                    'general_items',
                    'general_stock_adjustments.item_id',
                    '=',
                    'general_items.id'
                )
                ->leftJoin(
                    'users',
                    'general_stock_adjustments.adjusted_by',
                    '=',
                    'users.id'
                )
                ->where(
                    'general_items.category_id',
                    $categoryId
                )
                ->select(
                    'general_stock_adjustments.id',
                    'general_stock_adjustments.item_id',
                    DB::raw(
                        'DATE(general_stock_adjustments.created_at) as date'
                    ),
                    'general_items.name as item_name',
                    'general_stock_adjustments.adjustment_type as type',
                    'general_stock_adjustments.quantity',
                    'general_stock_adjustments.reason',
                    DB::raw(
                        'COALESCE(users.name, "Ward Nurse") as adjusted_by'
                    )
                )
                ->orderBy(
                    'general_stock_adjustments.id',
                    'desc'
                )
                ->get();

            if ($adjustments->count() > 0) {
                return $adjustments
                    ->map(function ($adj) {
                        $unit = self::guessUnitFromName(
                            $adj->item_name
                        );

                        $qty = (int) $adj->quantity;

                        $qtySign = $qty > 0
                            ? "+{$qty} {$unit}"
                            : "{$qty} {$unit}";

                        /*
                         * Calculate current balance for
                         * this exact item.
                         */
                        $received = (int) DB::table(
                            'general_transactions'
                        )
                            ->where(
                                'item_id',
                                $adj->item_id
                            )
                            ->sum('quantity_received');

                        $issued = (int) DB::table(
                            'general_transactions'
                        )
                            ->where(
                                'item_id',
                                $adj->item_id
                            )
                            ->sum('quantity_issued');

                        $balance = max(
                            0,
                            $received - $issued
                        );

                        return [
                            'id' => $adj->id,
                            'date' => $adj->date,
                            'item_name' =>
                                $adj->item_name,
                            'type' => $adj->type,
                            'quantity' => $qtySign,
                            'balance' =>
                                $balance . ' ' . $unit,
                            'reason' => $adj->reason,
                            'adjusted_by' =>
                                $adj->adjusted_by,
                        ];
                    })
                    ->toArray();
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Store a stock adjustment.
     *
     * Addition / surplus / return:
     *      increases stock.
     *
     * Damaged / lost / subtraction:
     *      decreases stock.
     */
    public static function storeAdjustment(
        $categoryId,
        $itemName,
        $type,
        $amount,
        $reason,
        $adjustedBy,
        $dateStr
    ) {
        self::ensureMasterRecords();

        $qty = abs((int) $amount);

        /*
         * $adjustedBy may be a user ID or a display name.
         *
         * If it is numeric, use it as the actual user ID.
         * Otherwise use the currently authenticated user.
         */
        $actualUserId = is_numeric($adjustedBy)
            ? (int) $adjustedBy
            : self::currentUserId();

        $isAddition = in_array(
            strtoupper((string) $type),
            [
                'COUNT_CORRECTION_ADD',
                'ADDITION',
                'SURPLUS',
                'RETURN',
            ],
            true
        );

        $receivedQty = $isAddition
            ? $qty
            : 0;

        $issuedQty = $isAddition
            ? 0
            : $qty;

        $note = sprintf(
            '[ADJUSTMENT:%s] %s | Adjusted by user ID: %d',
            strtoupper((string) $type),
            $reason,
            $actualUserId
        );

        /*
         * Store the corresponding inventory transaction.
         *
         * storeTransaction() will verify that the item
         * already exists in the database.
         */
        $transId = self::storeTransaction(
            $categoryId,
            $itemName,
            $receivedQty,
            $issuedQty,
            $dateStr,
            $note,
            $actualUserId
        );

        try {
            $item = DB::table('general_items')
                ->where(
                    'name',
                    strtoupper(trim($itemName))
                )
                ->where(
                    'category_id',
                    $categoryId
                )
                ->first();

            if ($item) {
                $adjType = 'COUNT_CORRECTION';

                if (
                    stripos(
                        (string) $type,
                        'DAMAGED'
                    ) !== false
                ) {
                    $adjType = 'DAMAGED';
                } elseif (
                    stripos(
                        (string) $type,
                        'LOSS'
                    ) !== false
                    ||
                    stripos(
                        (string) $type,
                        'LOST'
                    ) !== false
                ) {
                    $adjType = 'LOST';
                } elseif (
                    stripos(
                        (string) $type,
                        'RETURN'
                    ) !== false
                ) {
                    $adjType = 'RETURN';
                }

                $adjustmentData = [
                    'item_id' => $item->id,
                    'ward_id' => WardDatabaseService::getWardId(),
                    'adjustment_type' => $adjType,
                    'quantity' => $isAddition
                        ? $qty
                        : -$qty,
                    'reason' => $reason,
                    'adjusted_by' => $actualUserId,
                    'created_at' =>
                        self::normalizeDate($dateStr),
                ];

                /*
                 * If the migration has added transaction_id,
                 * link this adjustment to the exact transaction.
                 */
                try {
                    if (
                        Schema::hasColumn(
                            'general_stock_adjustments',
                            'transaction_id'
                        )
                    ) {
                        $adjustmentData[
                            'transaction_id'
                        ] = $transId;
                    }
                } catch (\Throwable $e) {
                    // Continue without optional column.
                }

                DB::table(
                    'general_stock_adjustments'
                )->insert($adjustmentData);
            }
        } catch (\Exception $e) {
            /*
             * Keep the inventory transaction even if
             * the secondary adjustment table fails.
             */
        }

        self::logActivity(
            "Stock adjustment ({$type}: {$qty}) "
            . "recorded for {$itemName}",
            $actualUserId
        );

        return $transId;
    }

    /**
     * Delete an adjustment and its exact
     * inventory transaction.
     */
    public static function deleteAdjustment($id)
    {
        try {
            $adjustment = DB::table(
                'general_stock_adjustments'
            )
                ->where('id', $id)
                ->first();

            if (!$adjustment) {
                return true;
            }

            /*
             * Preferred method:
             * delete the exact linked transaction.
             */
            try {
                if (
                    Schema::hasColumn(
                        'general_stock_adjustments',
                        'transaction_id'
                    )
                ) {
                    if (
                        !empty(
                            $adjustment->transaction_id
                        )
                    ) {
                        DB::table(
                            'general_transactions'
                        )
                            ->where(
                                'id',
                                $adjustment->transaction_id
                            )
                            ->delete();
                    }
                } else {
                    /*
                     * Compatibility fallback for old schema.
                     *
                     * Find the most recent matching transaction
                     * for the same item and adjustment quantity.
                     */
                    $quantity = abs(
                        (int) $adjustment->quantity
                    );

                    $isAddition =
                        (int) $adjustment->quantity > 0;

                    $query = DB::table(
                        'general_transactions'
                    )
                        ->where(
                            'item_id',
                            $adjustment->item_id
                        );

                    if ($isAddition) {
                        $query
                            ->where(
                                'quantity_received',
                                $quantity
                            )
                            ->where(
                                'quantity_issued',
                                0
                            );
                    } else {
                        $query
                            ->where(
                                'quantity_received',
                                0
                            )
                            ->where(
                                'quantity_issued',
                                $quantity
                            );
                    }

                    $transaction = $query
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($transaction) {
                        DB::table(
                            'general_transactions'
                        )
                            ->where(
                                'id',
                                $transaction->id
                            )
                            ->delete();
                    }
                }
            } catch (\Throwable $e) {
                // Continue to delete adjustment record.
            }

            DB::table(
                'general_stock_adjustments'
            )
                ->where('id', $id)
                ->delete();

            self::logActivity(
                'Deleted stock adjustment #' . $id
            );
        } catch (\Exception $e) {
            // Ignore delete failure.
        }

        return true;
    }

    /**
     * Get Daily Count records.
     *
     * READ-ONLY.
     *
     * Only records already stored in
     * general_transactions are returned.
     */
    public static function getDailyCounts(
        $categoryId
    ) {
        try {
            $records = DB::table(
                'general_transactions'
            )
                ->join(
                    'general_items',
                    'general_transactions.item_id',
                    '=',
                    'general_items.id'
                )
                ->where(
                    'general_items.category_id',
                    $categoryId
                )
                ->where(
                    'general_transactions.quantity_issued',
                    '>',
                    0
                )
                ->select(
                    'general_transactions.id',
                    'general_transactions.item_id',
                    DB::raw(
                        'DATE(general_transactions.date) as date'
                    ),
                    'general_items.name as item_name',
                    'general_transactions.quantity_issued as qty_given',
                    'general_transactions.date as created_at'
                );

            /*
             * Include Daily Count details when the migration
             * columns are available.
             */
            try {
                if (
                    Schema::hasColumn(
                        'general_transactions',
                        'sisters_initials'
                    )
                ) {
                    $records->addSelect(
                        'general_transactions.sisters_initials'
                    );
                }

                if (
                    Schema::hasColumn(
                        'general_transactions',
                        'remarks'
                    )
                ) {
                    $records->addSelect(
                        'general_transactions.remarks'
                    );
                }
            } catch (\Throwable $e) {
                // Continue without optional columns.
            }

            $records = $records
                ->orderBy(
                    'general_transactions.id',
                    'desc'
                )
                ->get();

            if ($records->count() > 0) {
                return $records
                    ->map(function ($r) {
                        $unit = self::guessUnitFromName(
                            $r->item_name
                        );

                        /*
                         * Calculate the balance for the
                         * exact item.
                         */
                        $received = (int) DB::table(
                            'general_transactions'
                        )
                            ->where(
                                'item_id',
                                $r->item_id
                            )
                            ->sum('quantity_received');

                        $issued = (int) DB::table(
                            'general_transactions'
                        )
                            ->where(
                                'item_id',
                                $r->item_id
                            )
                            ->sum('quantity_issued');

                        $balance = max(
                            0,
                            $received - $issued
                        );

                        return [
                            'id' => $r->id,
                            'date' => $r->date,
                            'item_name' =>
                                $r->item_name,
                            'qty_given' =>
                                (int) $r->qty_given
                                . ' ' . $unit,
                            'balance' =>
                                $balance . ' ' . $unit,
                            'sisters_initials' =>
                                $r->sisters_initials
                                ?? '',
                            'remarks' =>
                                $r->remarks
                                ?? '',
                        ];
                    })
                    ->toArray();
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Store Daily Count.
     *
     * Daily Count means stock has been given/used.
     *
     * Therefore:
     *
     * quantity_received = 0
     * quantity_issued   = quantity given
     *
     * This decreases available stock.
     */
    public static function storeDailyCount(
        $categoryId,
        $itemName,
        $qtyGiven,
        $sistersInitials,
        $remarks,
        $dateStr,
        $recordedBy = null
    ) {
        self::ensureMasterRecords();

        $qty = abs((int) $qtyGiven);

        $actualUserId = $recordedBy !== null
            ? (int) $recordedBy
            : self::currentUserId();

        /*
         * Find the item first.
         */
        $item = DB::table('general_items')
            ->where(
                'category_id',
                $categoryId
            )
            ->where(
                'name',
                strtoupper(trim($itemName))
            )
            ->first();

        if (!$item) {
            throw new \RuntimeException(
                'The inventory item could not be found.'
            );
        }

        /*
         * Calculate current balance before issuing stock.
         */
        $receivedBefore = (int) DB::table(
            'general_transactions'
        )
            ->where(
                'item_id',
                $item->id
            )
            ->sum('quantity_received');

        $issuedBefore = (int) DB::table(
            'general_transactions'
        )
            ->where(
                'item_id',
                $item->id
            )
            ->sum('quantity_issued');

        $currentBalance = max(
            0,
            $receivedBefore - $issuedBefore
        );

        /*
         * Do not allow Daily Count to issue more
         * stock than is currently available.
         */
        if ($qty > $currentBalance) {
            throw new \RuntimeException(
                'The quantity given cannot exceed '
                . 'the available stock balance of '
                . $currentBalance
                . '.'
            );
        }

        /*
         * Store Daily Count directly so that
         * sisters_initials and remarks are saved.
         */
        $transactionData = [
            'item_id' => $item->id,
            'ward_id' => WardDatabaseService::getWardId(),
            'quantity_received' => 0,
            'quantity_issued' => $qty,
            'date' => self::normalizeDate($dateStr),
            'recorded_by' => $actualUserId,
        ];

        /*
         * Add Daily Count fields if the migration exists.
         */
        try {
            if (
                Schema::hasColumn(
                    'general_transactions',
                    'sisters_initials'
                )
            ) {
                $transactionData[
                    'sisters_initials'
                ] = trim(
                    (string) $sistersInitials
                );
            }

            if (
                Schema::hasColumn(
                    'general_transactions',
                    'remarks'
                )
            ) {
                $transactionData[
                    'remarks'
                ] = trim(
                    (string) $remarks
                );
            }
        } catch (\Throwable $e) {
            // Continue.
        }

        $transId = DB::table(
            'general_transactions'
        )->insertGetId($transactionData);

        /*
         * Calculate the new balance.
         */
        $received = (int) DB::table(
            'general_transactions'
        )
            ->where(
                'item_id',
                $item->id
            )
            ->sum('quantity_received');

        $issued = (int) DB::table(
            'general_transactions'
        )
            ->where(
                'item_id',
                $item->id
            )
            ->sum('quantity_issued');

        $balance = max(
            0,
            $received - $issued
        );

        self::logActivity(
            "Daily count recorded for {$itemName}: "
            . "Qty {$qty}, "
            . "Sister {$sistersInitials}, "
            . "Balance {$balance}",
            $actualUserId
        );

        return [
            'id' => (int) $transId,
            'balance' => $balance,
            'quantity_received' => $received,
            'quantity_issued' => $issued,
            'recorded_by' => $actualUserId,
            'sisters_initials' => trim(
                (string) $sistersInitials
            ),
            'remarks' => trim(
                (string) $remarks
            ),
        ];
    }

    /**
     * Guess unit from item name.
     */
    private static function guessUnitFromName($name)
    {
        $name = strtoupper((string) $name);

        if (
            str_contains($name, 'GLOVES')
            ||
            str_contains($name, 'MASK')
        ) {
            return 'Box';
        }

        if (
            str_contains($name, 'ROLL')
            ||
            str_contains($name, 'BANDAGE')
            ||
            str_contains($name, 'WOOL')
            ||
            str_contains($name, 'PLASTER')
        ) {
            return 'Roll';
        }

        if (
            str_contains($name, 'BOTTLE')
            ||
            str_contains($name, 'IODINE')
            ||
            str_contains($name, 'SYRUP')
            ||
            str_contains($name, 'SANITIZER')
        ) {
            return 'Bottle';
        }

        if (str_contains($name, 'KIT')) {
            return 'Kit';
        }

        if (
            str_contains($name, 'PARACETAMOL')
            ||
            str_contains($name, 'TABS')
        ) {
            return 'Tabs';
        }

        return 'Pcs';
    }
}