<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyBulkPatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dummyPatient = \App\Models\Patient::firstOrCreate(
            ['patient_name' => 'Ward General Use']
        );

        $wardId = \Illuminate\Support\Facades\DB::table('wards')->value('id') ?? 1;

        \App\Models\Admission::firstOrCreate(
            ['bht_no' => 'BULK-GENERAL'],
            [
                'patient_id' => $dummyPatient->id,
                'ward_id' => $wardId,
                'status' => 'Admitted'
            ]
        );
    }
}
