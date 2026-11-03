<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OptimizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_id' => ['required', 'integer', Rule::exists('batches', 'id')],
            'source_site_id' => ['required', 'integer', Rule::exists('sites', 'id')],
            'quantity' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function attributes(): array
    {
        return ['batch_id' => __('lot'), 'source_site_id' => __('site d’origine'), 'quantity' => __('quantité')];
    }
}
