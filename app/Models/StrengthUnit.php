<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StrengthUnit extends Model
{
    protected $table = 'strength_units';
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class, 'strength_unit_id');
    }

    /**
     * Ensure the strength_units table, default seed values, and medicines columns exist.
     */
    public static function ensureInitialized(): void
    {
        try {
            if (!Schema::hasTable('strength_units')) {
                Schema::create('strength_units', function (Blueprint $table) {
                    $table->integer('id', true);
                    $table->string('name', 20)->unique();
                });

                DB::table('strength_units')->insert([
                    ['id' => 1, 'name' => 'mg'],
                    ['id' => 2, 'name' => 'ml'],
                    ['id' => 3, 'name' => 'g'],
                    ['id' => 4, 'name' => 'mcg'],
                    ['id' => 5, 'name' => 'IU'],
                    ['id' => 6, 'name' => '%'],
                ]);
            } elseif (DB::table('strength_units')->count() === 0) {
                DB::table('strength_units')->insert([
                    ['id' => 1, 'name' => 'mg'],
                    ['id' => 2, 'name' => 'ml'],
                    ['id' => 3, 'name' => 'g'],
                    ['id' => 4, 'name' => 'mcg'],
                    ['id' => 5, 'name' => 'IU'],
                    ['id' => 6, 'name' => '%'],
                ]);
            }

            // Auto-standardize medicine_forms, units, and strength_units if legacy mixed values still exist in units
            $hasLegacyUnits = Schema::hasTable('units') && DB::table('units')
                ->whereIn('unit_name', ['mg', 'ml', 'g', 'mcg', 'IU', 'Tablet', 'Capsule'])
                ->exists();

            $missingConsumableForm = Schema::hasTable('medicine_forms') && !DB::table('medicine_forms')
                ->where('form_name', 'Consumable')
                ->exists();

            if ($hasLegacyUnits || $missingConsumableForm) {
                (new \Database\Seeders\StandardizeMasterDataSeeder())->run();
            }

            if (Schema::hasTable('medicines')) {
                $hasValue = Schema::hasColumn('medicines', 'strength_value');
                $hasUnitId = Schema::hasColumn('medicines', 'strength_unit_id');

                if (!$hasValue || !$hasUnitId) {
                    Schema::table('medicines', function (Blueprint $table) use ($hasValue, $hasUnitId) {
                        if (!$hasValue) {
                            $table->decimal('strength_value', 10, 2)->nullable()->after('form_id');
                        }
                        if (!$hasUnitId) {
                            $table->integer('strength_unit_id')->nullable()->after('strength_value');
                            $table->foreign('strength_unit_id')
                                ->references('id')
                                ->on('strength_units')
                                ->onDelete('restrict');
                        }
                    });
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore schema check issues if DB connection is unavailable
        }
    }
}
