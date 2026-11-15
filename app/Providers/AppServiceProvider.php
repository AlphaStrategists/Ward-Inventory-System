<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('add_medicine', function ($user = null) {

            if (!$user) {
                return false;
            }

            // Admin can add medicines.
            if ($user->isAdmin()) {
                return true;
            }

            // Get role directly from users.role.
            $userRole = trim((string) $user->role);

            if ($userRole === '') {
                return false;
            }

            // Doctor and MS are equivalent.
            if (strtolower($userRole) === 'doctor') {
                $userRole = 'MS';
            }

            // Find the matching role.
            $role = Role::whereRaw(
                'LOWER(TRIM(role_name)) = ?',
                [strtolower($userRole)]
            )->first();

            if (!$role) {
                return false;
            }

            return $role->permissions()
                ->where('permission_name', 'add_medicine')
                ->exists();
        });

        // Inventory ledger synchronization observers.
        \App\Models\StockReceipt::observe(
            \App\Observers\StockReceiptObserver::class
        );

        \App\Models\Dispensation::observe(
            \App\Observers\DispensationObserver::class
        );

        \App\Models\MedicineStockAdjustment::observe(
            \App\Observers\MedicineStockAdjustmentObserver::class
        );

        \App\Models\StockAdjustment::observe(
            \App\Observers\MedicineStockAdjustmentObserver::class
        );
    }
}
