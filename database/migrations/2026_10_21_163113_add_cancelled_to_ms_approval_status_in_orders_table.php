<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            return; // ENUM / MODIFY is MySQL-only
        }

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `orders` MODIFY `ms_approval_status` ENUM('Pending', 'Completed', 'Approved', 'Rejected', 'Cancelled') NOT NULL DEFAULT 'Pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            return;
        }

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `orders` MODIFY `ms_approval_status` ENUM('Pending', 'Completed', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending'");
    }
};
