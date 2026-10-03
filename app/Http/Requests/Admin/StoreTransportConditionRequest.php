<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransportConditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_id' => ['required', 'exists:batches,id'],
            'traceability_event_id' => ['nullable', 'exists:traceability_events,id'],
            'recorded_at' => ['required', 'date'],
            'temperature' => ['required', 'numeric', 'between:-50,60'],
            'humidity' => ['nullable', 'numeric', 'between:0,100'],
            'location' => ['nullable', 'string', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
