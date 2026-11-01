<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_name' => ['required', 'string', 'max:150'],
            'bht_no' => ['nullable', 'string', 'max:50', 'unique:admissions,bht_no'],
            'ward_id' => ['nullable', 'integer', 'exists:wards,id'],
            'admit_date' => ['nullable', 'date'],
        ];
    }
}
