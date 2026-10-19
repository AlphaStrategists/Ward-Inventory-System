<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->whereRaw('LOWER(role) = ?', ['nurse'])->update(['role' => 'Nurse']);
        DB::table('users')->whereIn('role', ['staff_nurse', 'Staff_Nurse', 'staff nurse'])->update(['role' => 'Staff Nurse']);
        DB::table('users')->whereIn('role', ['ward_sister', 'Ward_Sister', 'ward sister'])->update(['role' => 'Ward Sister']);
    }

    public function down(): void
    {
        // not reversible
    }
};