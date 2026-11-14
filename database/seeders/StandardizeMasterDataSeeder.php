<?php

namespace Database\Seeders;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StandardizeMasterDataSeeder extends Seeder
{
    /**
     * Clean and standardize Master Data tables (medicine_forms, units, strength_units).
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // Ensure strength_units table exists before truncating
        if (!Schema::hasTable('strength_units')) {
            Schema::create('strength_units', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('name', 20)->unique();
            });
        }

        // 1. Truncate existing records in all three Master Data tables
        DB::table('medicine_forms')->truncate();
        DB::table('units')->truncate();
        DB::table('strength_units')->truncate();

        // 2. Seed standardized medicine_forms (The physical form of the drug)
        DB::table('medicine_forms')->insert([
            ['id' => 1, 'form_name' => 'Tablet'],
            ['id' => 2, 'form_name' => 'Capsule'],
            ['id' => 3, 'form_name' => 'Syrup'],
            ['id' => 4, 'form_name' => 'Injection'],
            ['id' => 5, 'form_name' => 'Cream'],
            ['id' => 6, 'form_name' => 'Drops'],
            ['id' => 7, 'form_name' => 'Consumable'],
        ]);

        // 3. Seed standardized units (The inventory/packaging unit)
        DB::table('units')->insert([
            ['id' => 1, 'unit_name' => 'Piece (Pcs)'],
            ['id' => 2, 'unit_name' => 'Bottle'],
            ['id' => 3, 'unit_name' => 'Vial'],
            ['id' => 4, 'unit_name' => 'Ampoule'],
            ['id' => 5, 'unit_name' => 'Box'],
            ['id' => 6, 'unit_name' => 'Pack'],
            ['id' => 7, 'unit_name' => 'Roll'],
            ['id' => 8, 'unit_name' => 'Kit'],
        ]);

        // 4. Seed standardized strength_units (The measurement/concentration unit)
        DB::table('strength_units')->insert([
            ['id' => 1, 'name' => 'mg'],
            ['id' => 2, 'name' => 'ml'],
            ['id' => 3, 'name' => 'g'],
            ['id' => 4, 'name' => 'mcg'],
            ['id' => 5, 'name' => 'IU'],
            ['id' => 6, 'name' => '%'],
        ]);

        Schema::enableForeignKeyConstraints();
    }
}

