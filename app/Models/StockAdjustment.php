<?php

namespace App\Models;

class StockAdjustment extends MedicineStockAdjustment
{
    // Alias / subclass for MedicineStockAdjustment
}

if (!class_exists('StockAdjustment')) {
    class_alias(StockAdjustment::class, 'StockAdjustment');
}
