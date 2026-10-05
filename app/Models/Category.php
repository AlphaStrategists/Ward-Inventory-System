<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'categories';
    const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'description',
    ];

    public function generalItems(): HasMany
    {
        return $this->hasMany(GeneralItem::class, 'category_id');
    }

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class, 'category_id');
    }
}
