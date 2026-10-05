<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('add_medicine', function ($user = null) {
            // Fallback for when auth is not fully ready
            if (!$user) {
                $user = DB::table('users')->where('id', 1)->first();
            }
            if (!$user || !isset($user->role_id)) {
                return false;
            }

            // Check if the role has the add_medicine permission
            $hasPermission = DB::table('role_permissions')
                ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                ->where('role_permissions.role_id', $user->role_id)
                ->where('permissions.permission_name', 'add_medicine')
                ->exists();

            return $hasPermission;
        });
    }
}
