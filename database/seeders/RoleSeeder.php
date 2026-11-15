<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Ensure canonical roles exist and safely consolidate legacy role aliases.
     * Existing role records and permissions are retained; no users are reset.
     */
    public function run(): void
    {
        $canonical = [];
        foreach (['Admin', 'MS', 'Nurse', 'Pharmacist'] as $name) {
            $canonical[$name] = Role::firstOrCreate(['role_name' => $name]);
        }

        $this->mergeRoleAliases(['Doctor', 'Medical Officer', 'Test Doctor'], $canonical['MS']->id);
        $this->mergeRoleAliases(['Staff Nurse', 'Ward Sister', 'Test Nurse', 'Audit Nurse', 'Ctrl Nurse'], $canonical['Nurse']->id);
        $this->mergeRoleAliases(['Test Pharmacist'], $canonical['Pharmacist']->id);
        $this->mergeRoleAliases(['System Admin'], $canonical['Admin']->id);

        if (DB::getSchemaBuilder()->hasColumn('users', 'role')) {
            DB::table('users')->whereRaw('LOWER(TRIM(role)) = ?', ['doctor'])->update(['role' => 'MS']);
        }
    }

    private function mergeRoleAliases(array $aliases, int $targetRoleId): void
    {
        $aliasIds = Role::whereIn('role_name', $aliases)->pluck('id');
        if ($aliasIds->isEmpty()) {
            return;
        }

        if (DB::getSchemaBuilder()->hasTable('role_permissions')) {
            $permissionIds = DB::table('role_permissions')
                ->whereIn('role_id', $aliasIds)
                ->pluck('permission_id')
                ->unique();

            foreach ($permissionIds as $permissionId) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_id' => $targetRoleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        DB::table('users')->whereIn('role_id', $aliasIds)->update(['role_id' => $targetRoleId]);
    }
}
