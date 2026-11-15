<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Use category names as stable identifiers; never assume numeric IDs.
        foreach ([
            'Medical Consumables' => 'Inventory for Surgical Consumables 1',
            'Controlled Narcotics' => 'Inventory for Surgical Consumables 2',
            'Local Purchases' => 'Inventory for locally purchased items',
        ] as $name => $description) {
            if (!DB::table('categories')->where('name', $name)->exists()) {
                DB::table('categories')->insert([
                    'name' => $name,
                    'description' => $description,
                    'created_at' => now(),
                ]);
            }
        }

        if (!Schema::hasColumn('orders', 'module')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('module', 50)->nullable()->after('ward_id');
                $table->index('module', 'orders_module_index');
            });
        }

        // Recover module tags already stored in the JSON remark field.
        // Rows with no module metadata are deliberately left NULL for manual classification.
        if (Schema::hasColumn('orders', 'remark')) {
            DB::table('orders')
                ->whereNull('module')
                ->whereNotNull('remark')
                ->orderBy('id')
                ->chunkById(100, function ($orders) {
                    foreach ($orders as $order) {
                        $metadata = json_decode((string) $order->remark, true);
                        $module = is_array($metadata) ? ($metadata['module'] ?? null) : null;

                        if (in_array($module, ['surgical_consumable_1', 'surgical_consumable_2', 'local_purchase'], true)) {
                            DB::table('orders')->where('id', $order->id)->update(['module' => $module]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'module')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_module_index');
                $table->dropColumn('module');
            });
        }

        // Categories are intentionally retained on rollback because inventory may reference them.
    }
};
