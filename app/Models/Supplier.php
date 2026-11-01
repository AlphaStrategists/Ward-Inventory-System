<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = ['supplier_name', 'contact_info', 'created_at'];
    const UPDATED_AT = null;

    public function medicineBatches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class, 'supplier_id');
    }
}
