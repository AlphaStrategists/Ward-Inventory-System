<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGeneralItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $itemId = $this->route('general_item') ? $this->route('general_item')->id : null;

        return [
            'item_code' => ['required', 'string', 'max:50', Rule::unique('general_items', 'item_code')->ignore($itemId)],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ];
    }
}
