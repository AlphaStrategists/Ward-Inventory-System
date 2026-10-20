<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->whereRaw('LOWER(role) = ?', ['nurse'])->update(['role' => 'Nurse']);
        DB::table('users')->whereIn('role', ['Staff Nurse', 'Staff Nurse', 'Staff Nurse'])->update(['role' => 'Staff Nurse']);
        DB::table('users')->whereIn('role', ['Ward Sister', 'Ward Sister', 'Ward Sister'])->update(['role' => 'Ward Sister']);
    }

    public function down(): void
    {
        // not reversible
    }
};