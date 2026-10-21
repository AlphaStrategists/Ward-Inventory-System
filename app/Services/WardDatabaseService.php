<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class WardDatabaseService
{
    /**
     * Ensure master lookup records exist in the 25 tables.
     */
    public static function ensureMasterRecords()
    {
        try {
            // 1. Role
            $roleExists = DB::table('roles')->where('id', 1)->exists();
            if (!$roleExists) {
                DB::table('roles')->insertOrIgnore([
                    'id' => 1,
                    'role_name' => 'Ward In-Charge',
                    'created_at' => now(),
                ]);
            }

            // 2. Ward
            $wardExists = DB::table('wards')->where('id', 1)->exists();
            if (!$wardExists) {
                DB::table('wards')->insertOrIgnore([
                    'id' => 1,
                    'ward_number' => 'W-48',
                    'ward_name' => 'Ward 48 - General',
                ]);
            }

            // 3. User
            $userExists = DB::table('users')->where('id', 1)->exists();
            if (!$userExists) {
                DB::table('users')->insertOrIgnore([
                    'id' => 1,
                    'name' => 'Sr. Perera (RN-402)',
                    'email' => 'officer@ward48.local',
                    'password' => bcrypt('password'),
                    'role_id' => 1,
                    'ward_id' => 1,
                    'created_at' => now(),
                ]);
            }

            // 4. Categories
            $categories = [
                1 => 'Surgical Consumables 1',
                2 => 'Surgical Consumables 2',
                3 => 'Local Purchases',
            ];

            foreach ($categories as $id => $name) {
                $catExists = DB::table('categories')->where('id', $id)->exists();
                if (!$catExists) {
                    DB::table('categories')->insertOrIgnore([
                        'id' => $id,
                        'name' => $name,
                        'description' => $name . ' Category',
                        'created_at' => now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Ignore if tables not found
        }
    }

    /**
     * Seed initial items for a category if none exist.
     */
    public static function seedCategoryItemsIfEmpty($categoryId, array $initialItems)
    {
        self::ensureMasterRecords();

        try {
            $count = DB::table('general_items')->where('category_id', $categoryId)->count();
            if ($count === 0) {
                foreach ($initialItems as $index => $item) {
                    $itemCode = sprintf('CAT%d-%03d', $categoryId, $index + 1);
                    $itemId = DB::table('general_items')->insertGetId([
                        'item_code' => $itemCode,
                        'name' => strtoupper($item['name']),
                        'category_id' => $categoryId,
                    ]);

                    // Add initial stock receipt transaction
                    $initialBalance = isset($item['balance']) ? (int) $item['balance'] : 0;
                    if ($initialBalance > 0) {
                        DB::table('general_transactions')->insert([
                            'item_id' => $itemId,
                            'ward_id' => 1,
                            'quantity_received' => $initialBalance,
                            'quantity_issued' => 0,
                            'date' => now(),
                            'recorded_by' => 1,
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore
        }
    }

    /**
     * Get all items for a category with calculated balances.
     */
    public static function getCategoryItems($categoryId, array $defaultFallback = [])
    {
        self::ensureMasterRecords();

        try {
            $items = DB::table('general_items')
                ->where('category_id', $categoryId)
                ->get();

            if ($items->count() > 0) {
                $result = [];
                foreach ($items as $item) {
                    $received = DB::table('general_transactions')
                        ->where('item_id', $item->id)
                        ->sum('quantity_received');
                    $issued = DB::table('general_transactions')
                        ->where('item_id', $item->id)
                        ->sum('quantity_issued');
                    $balance = max(0, $received - $issued);

                    $result[] = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'balance' => (int) $balance,
                        'unit' => self::guessUnitFromName($item->name),
                    ];
                }
                return $result;
            }
        } catch (\Exception $e) {
            // Fallback
        }

        // Seed fallback data into database
        if (!empty($defaultFallback)) {
            self::seedCategoryItemsIfEmpty($categoryId, $defaultFallback);
            return self::getCategoryItems($categoryId, []);
        }

        return [];
    }

    /**
     * Add a new item into general_items.
     */
    public static function storeItem($categoryId, $name, $balance, $unit)
    {
        self::ensureMasterRecords();

        $name = strtoupper(trim($name));
        $itemCode = sprintf('CAT%d-%d-%04d', $categoryId, time(), mt_rand(100, 999));

        try {
            $itemId = DB::table('general_items')->insertGetId([
                'item_code' => $itemCode,
                'name' => $name,
                'category_id' => $categoryId,
            ]);

            if ($balance > 0) {
                DB::table('general_transactions')->insert([
                    'item_id' => $itemId,
                    'ward_id' => 1,
                    'quantity_received' => (int) $balance,
                    'quantity_issued' => 0,
                    'date' => now(),
                    'recorded_by' => 1,
                ]);
            }

            // Log activity
            self::logActivity('Added item: ' . $name);

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
            ->update(['name' => $name]);

        self::logActivity('Updated item #' . $itemId . ' name to ' . $name);

        return true;
    }

    /**
     * Delete item.
     */
    public static function deleteItem($itemId)
    {
        self::ensureMasterRecords();

        DB::table('general_transactions')->where('item_id', $itemId)->delete();
        DB::table('general_items')->where('id', $itemId)->delete();

        self::logActivity('Deleted item #' . $itemId);

        return true;
    }

    /**
     * Add a transaction (Issue or Receipt).
     */
    public static function storeTransaction($categoryId, $itemName, $receivedQty, $issuedQty, $dateStr, $note = '')
    {
        self::ensureMasterRecords();

        // Find or create item
        $item = DB::table('general_items')
            ->where('name', strtoupper(trim($itemName)))
            ->where('category_id', $categoryId)
            ->first();

        if (!$item) {
            $itemId = DB::table('general_items')->insertGetId([
                'item_code' => sprintf('CAT%d-%d', $categoryId, time()),
                'name' => strtoupper(trim($itemName)),
                'category_id' => $categoryId,
            ]);
        } else {
            $itemId = $item->id;
        }

        $transId = DB::table('general_transactions')->insertGetId([
            'item_id' => $itemId,
            'ward_id' => 1,
            'quantity_received' => (int) $receivedQty,
            'quantity_issued' => (int) $issuedQty,
            'date' => $dateStr ? date('Y-m-d H:i:s', strtotime($dateStr)) : now(),
            'recorded_by' => 1,
        ]);

        self::logActivity('Recorded transaction for item: ' . $itemName . ' (Received: ' . $receivedQty . ', Issued: ' . $issuedQty . ')');

        return $transId;
    }

    /**
     * Log activity to activity_logs.
     */
    public static function logActivity($action)
    {
        try {
            DB::table('activity_logs')->insert([
                'user_id' => 1,
                'action_performed' => $action,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Ignore
        }
    }

    /**
     * Get stock adjustments for category from initial general_stock_adjustments table.
     */
    public static function getAdjustments($categoryId, array $fallback = [])
    {
        try {
            $adjustments = DB::table('general_stock_adjustments')
                ->join('general_items', 'general_stock_adjustments.item_id', '=', 'general_items.id')
                ->leftJoin('users', 'general_stock_adjustments.adjusted_by', '=', 'users.id')
                ->where('general_items.category_id', $categoryId)
                ->select(
                    'general_stock_adjustments.id',
                    DB::raw('DATE(general_stock_adjustments.created_at) as date'),
                    'general_items.name as item_name',
                    'general_stock_adjustments.adjustment_type as type',
                    'general_stock_adjustments.quantity',
                    'general_stock_adjustments.reason',
                    DB::raw('COALESCE(users.name, "Ward Nurse") as adjusted_by')
                )
                ->orderBy('general_stock_adjustments.id', 'desc')
                ->get();

            if ($adjustments->count() > 0) {
                return $adjustments->map(function ($adj) {
                    $unit = self::guessUnitFromName($adj->item_name);
                    $qtySign = $adj->quantity > 0 ? "+{$adj->quantity} {$unit}" : "{$adj->quantity} {$unit}";
                    return [
                        'id' => $adj->id,
                        'date' => $adj->date,
                        'item_name' => $adj->item_name,
                        'type' => $adj->type,
                        'quantity' => $qtySign,
                        'balance' => "{$unit}",
                        'reason' => $adj->reason,
                        'adjusted_by' => $adj->adjusted_by,
                    ];
                })->toArray();
            }
        } catch (\Exception $e) {
            // DB not connected or empty, use fallback
        }

        return $fallback;
    }

    /**
     * Store a stock adjustment transaction into initial general_stock_adjustments & general_transactions tables.
     */
    public static function storeAdjustment($categoryId, $itemName, $type, $amount, $reason, $adjustedBy, $dateStr)
    {
        self::ensureMasterRecords();

        $qty = abs((int) $amount);
        $isAddition = in_array(strtoupper($type), ['COUNT_CORRECTION_ADD', 'ADDITION', 'SURPLUS', 'RETURN']);

        $receivedQty = $isAddition ? $qty : 0;
        $issuedQty = $isAddition ? 0 : $qty;

        $note = sprintf('[ADJUSTMENT:%s] %s | Adjusted by: %s', strtoupper($type), $reason, $adjustedBy);

        $transId = self::storeTransaction(
            $categoryId,
            $itemName,
            $receivedQty,
            $issuedQty,
            $dateStr,
            $note
        );

        // Also record in the initial database table general_stock_adjustments
        try {
            $item = DB::table('general_items')
                ->where('name', strtoupper(trim($itemName)))
                ->where('category_id', $categoryId)
                ->first();

            if ($item) {
                $adjType = 'COUNT_CORRECTION';
                if (stripos($type, 'DAMAGED') !== false) $adjType = 'DAMAGED';
                elseif (stripos($type, 'LOSS') !== false || stripos($type, 'LOST') !== false) $adjType = 'LOST';
                elseif (stripos($type, 'RETURN') !== false) $adjType = 'RETURN';

                DB::table('general_stock_adjustments')->insert([
                    'item_id' => $item->id,
                    'ward_id' => 1,
                    'adjustment_type' => $adjType,
                    'quantity' => $isAddition ? $qty : -$qty,
                    'reason' => $reason . ' (Adjusted by: ' . $adjustedBy . ')',
                    'adjusted_by' => 1,
                    'created_at' => $dateStr ? date('Y-m-d H:i:s', strtotime($dateStr)) : now(),
                ]);
            }
        } catch (\Exception $e) {
            // Ignore if DB not reachable
        }

        self::logActivity("Stock adjustment ({$type}: {$qty}) recorded for {$itemName} by {$adjustedBy}");

        return $transId;
    }

    /**
     * Delete an adjustment record from initial tables.
     */
    public static function deleteAdjustment($id)
    {
        try {
            DB::table('general_stock_adjustments')->where('id', $id)->delete();
            DB::table('general_transactions')->where('id', $id)->delete();
        } catch (\Exception $e) {
            // Ignore
        }
        return true;
    }

    /**
     * Get daily counts for category from general_transactions (issues).
     */
    public static function getDailyCounts($categoryId, array $fallback = [])
    {
        try {
            $records = DB::table('general_transactions')
                ->join('general_items', 'general_transactions.item_id', '=', 'general_items.id')
                ->where('general_items.category_id', $categoryId)
                ->where('general_transactions.quantity_issued', '>', 0)
                ->select(
                    'general_transactions.id',
                    DB::raw('DATE(general_transactions.date) as date'),
                    'general_items.name as item_name',
                    'general_transactions.quantity_issued as qty_given',
                    'general_transactions.date as created_at'
                )
                ->orderBy('general_transactions.id', 'desc')
                ->get();

            if ($records->count() > 0) {
                return $records->map(function ($r) use ($categoryId) {
                    $unit = self::guessUnitFromName($r->item_name);
                    return [
                        'id' => $r->id,
                        'date' => $r->date,
                        'item_name' => $r->item_name,
                        'qty_given' => "{$r->qty_given} {$unit}",
                        'balance' => "{$unit}",
                        'sisters_initials' => 'K.P.',
                        'remarks' => 'Daily ward distribution',
                    ];
                })->toArray();
            }
        } catch (\Exception $e) {
            // DB not connected, use fallback
        }

        return $fallback;
    }

    /**
     * Store a daily count transaction into general_transactions.
     */
    public static function storeDailyCount($categoryId, $itemName, $qtyGiven, $sistersInitials, $remarks, $dateStr)
    {
        self::ensureMasterRecords();

        $qty = abs((int) $qtyGiven);
        $note = sprintf('[DAILY_COUNT] Qty given: %d | Sister: %s | Remarks: %s', $qty, $sistersInitials, $remarks);

        $transId = self::storeTransaction(
            $categoryId,
            $itemName,
            0,
            $qty,
            $dateStr,
            $note
        );

        self::logActivity("Daily count recorded for {$itemName}: Qty {$qty}, Sister {$sistersInitials}");

        return $transId;
    }

    /**
     * Helper to guess unit from name.
     */
    private static function guessUnitFromName($name)
    {
        if (str_contains($name, 'GLOVES') || str_contains($name, 'MASK')) return 'Box';
        if (str_contains($name, 'ROLL') || str_contains($name, 'BANDAGE') || str_contains($name, 'WOOL') || str_contains($name, 'PLASTER')) return 'Roll';
        if (str_contains($name, 'BOTTLE') || str_contains($name, 'IODINE') || str_contains($name, 'SYRUP') || str_contains($name, 'SANITIZER')) return 'Bottle';
        if (str_contains($name, 'KIT')) return 'Kit';
        if (str_contains($name, 'PARACETAMOL') || str_contains($name, 'TABS')) return 'Tabs';
        return 'Pcs';
    }
}
