<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGeneralTransactionRequest extends FormRequest
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
            'transaction_type' => ['required', 'in:RECEIPT,ISSUE'],
            'quantity' => ['required', 'integer', 'min:1'],
            'date' => ['required', 'date'],
            'recorded_by' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
