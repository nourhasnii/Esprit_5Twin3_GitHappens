<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['min_threshold' => ['required', 'numeric', 'min:0']];
    }

    public function attributes(): array
    {
        return ['min_threshold' => 'seuil minimum'];
    }
}
