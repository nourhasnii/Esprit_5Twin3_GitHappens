<?php

namespace App\Http\Requests\Admin;

use App\Models\Alert;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_id' => ['nullable', 'exists:batches,id'],
            'transport_condition_id' => ['nullable', 'exists:transport_conditions,id'],
            'type' => ['required', Rule::in(Alert::TYPES)],
            'severity' => ['required', Rule::in(Alert::SEVERITIES)],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'status' => ['required', Rule::in(Alert::STATUSES)],
            'risk_score' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }
}
