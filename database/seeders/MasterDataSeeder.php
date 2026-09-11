<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Unit;
use App\Models\MedicineForm;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['antibiotics', 'oral-antibiotics', 'syrups', 'narcotics', 'iv-fluids', 'OralCountable'];
        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat]);
        }

        $units = ['Tablets', 'Capsules', 'Bottles', 'Ampoules', 'Vials', 'Bags'];
        foreach ($units as $u) {
            Unit::firstOrCreate(['unit_name' => $u]);
        }

        $forms = ['Tablet', 'Capsule', 'Syrup', 'Ampoule', 'Vial', 'IV Fluid'];
        foreach ($forms as $f) {
            MedicineForm::firstOrCreate(['form_name' => $f]);
        }
    }
}
