<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $admissionId = $this->route('admission') ? $this->route('admission')->id : null;

        return [
            'bht_no' => ['required', 'string', 'max:50', Rule::unique('admissions', 'bht_no')->ignore($admissionId)],
            'ward_id' => ['required', 'integer', 'exists:wards,id'],
            'admit_date' => ['required', 'date'],
            'status' => ['required', 'in:Admitted,Discharged,Transferred'],
        ];
    }
}
