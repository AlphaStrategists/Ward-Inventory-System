<?php

namespace App\Http\Requests;

use App\Models\MedicineBatch;
use Illuminate\Foundation\Http\FormRequest;

class StoreDispensationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admission_id' => ['required', 'integer', 'exists:admissions,id'],
            'batch_id' => [
                'required',
                'integer',
                'exists:medicine_batches,id',
                function ($attribute, $value, $fail) {
                    $batch = MedicineBatch::with('medicine')->find($value);
                    if (!$batch || !$batch->medicine || !$batch->medicine->is_controlled) {
                        $fail('The selected medicine batch is not a verified controlled/narcotic substance.');
                    }
                },
            ],
            'date' => ['required', 'date'],
            'qty_given' => ['required', 'integer', 'min:1'],
            'dosage' => ['required', 'string', 'max:100'],
            'usage_time' => ['nullable', 'string', 'max:100'],
            'issued_by' => ['required', 'integer', 'exists:users,id'],
            'witnessed_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'batch_id.required' => 'Please select a narcotic medicine batch.',
            'qty_given.min' => 'Quantity dispensed must be at least 1.',
            'dosage.required' => 'Clinical dosage instructions are required.',
            'issued_by.required' => 'An authorized issuing officer must be selected.',
        ];
    }
}
