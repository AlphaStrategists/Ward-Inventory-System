<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGeneralStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:general_items,id'],
            'ward_id' => ['required', 'integer', 'exists:wards,id'],
            'adjustment_type' => ['required', 'in:DAMAGED,LOST,COUNT_CORRECTION,RETURN'],
            'quantity' => ['required', 'integer'],
            'reason' => ['required', 'string', 'max:1000'],
            'adjusted_by' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
