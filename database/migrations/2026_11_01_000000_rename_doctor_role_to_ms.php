<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('roles')) {
            $doctorRole = DB::table('roles')->where('role_name', 'Doctor')->first();
            $msRole = DB::table('roles')->where('role_name', 'MS')->first();

            if ($doctorRole && $msRole) {
                if (Schema::hasTable('users') && Schema::hasColumn('users', 'role_id')) {
                    DB::table('users')
                        ->where('role_id', $doctorRole->id)
                        ->update(['role_id' => $msRole->id]);
                }

                if (Schema::hasTable('role_permissions')) {
                    $existingPerms = DB::table('role_permissions')
                        ->where('role_id', $msRole->id)
                        ->pluck('permission_id')
                        ->all();

                    DB::table('role_permissions')
                        ->where('role_id', $doctorRole->id)
                        ->whereNotIn('permission_id', $existingPerms)
                        ->update(['role_id' => $msRole->id]);

                    DB::table('role_permissions')
                        ->where('role_id', $doctorRole->id)
                        ->delete();
                }

                DB::table('roles')->where('id', $doctorRole->id)->delete();
            } elseif ($doctorRole) {
                DB::table('roles')
                    ->where('id', $doctorRole->id)
                    ->update(['role_name' => 'MS']);
            }
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            DB::table('users')
                ->whereRaw('LOWER(role) = ?', ['doctor'])
                ->update(['role' => 'MS']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('roles')) {
            $msRole = DB::table('roles')->where('role_name', 'MS')->first();
            $doctorRole = DB::table('roles')->where('role_name', 'Doctor')->first();

            if ($msRole && !$doctorRole) {
                DB::table('roles')
                    ->where('id', $msRole->id)
                    ->update(['role_name' => 'Doctor']);
            }
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            DB::table('users')
                ->whereRaw('LOWER(role) = ?', ['ms'])
                ->update(['role' => 'Doctor']);
        }
    }
};

