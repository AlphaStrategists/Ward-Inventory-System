<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'role')) {
            DB::table('users')->whereRaw('LOWER(role) IN (?, ?, ?)', ['nurse', 'staff nurse', 'ward sister'])->update(['role' => 'Nurse']);
        }
    }

    public function down(): void
    {
        // not reversible
    }
};