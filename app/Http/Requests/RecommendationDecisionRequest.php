<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecommendationDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['accept', 'modify', 'reject'])],
            'chosen_site_id' => ['nullable', 'required_if:action,modify', 'integer', Rule::exists('sites', 'id')],
            'note' => ['nullable', 'required_if:action,modify,reject', 'string', 'max:1000'],
            'create_movement' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'chosen_site_id.required_if' => 'Choisissez le site de destination retenu.',
            'note.required_if' => 'Expliquez en une phrase pourquoi vous modifiez ou rejetez la suggestion.',
        ];
    }
}
