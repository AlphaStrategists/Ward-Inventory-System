<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'bht_no' => ['required', 'string', 'max:50', 'unique:admissions,bht_no'],
            'ward_id' => ['required', 'integer', 'exists:wards,id'],
            'admit_date' => ['required', 'date'],
            'status' => ['required', 'in:Admitted,Discharged,Transferred'],
        ];
    }
}
