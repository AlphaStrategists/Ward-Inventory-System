<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Truncates all test medicines and related transactional tables
     * while preserving all Master Data (users, roles, categories, units,
     * medicine_forms, strength_units, suppliers, wards).
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // Truncate transaction and ledger tables first (exact table names from models)
        DB::table('medicine_stock_adjustments')->truncate();
        DB::table('dispensations')->truncate();
        DB::table('stock_receipts')->truncate();
        DB::table('order_details')->truncate();
        DB::table('orders')->truncate();
        DB::table('stock_ledger')->truncate();

        // Truncate batches and finally medicines
        DB::table('medicine_batches')->truncate();
        DB::table('medicines')->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
