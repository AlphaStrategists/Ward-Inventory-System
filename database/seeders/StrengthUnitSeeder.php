<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StrengthUnitSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('strength_units')->truncate();

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
