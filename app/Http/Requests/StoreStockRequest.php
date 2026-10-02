<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_id' => ['required', 'integer', Rule::exists('sites', 'id')],
            'product_id' => ['nullable', 'required_without:batch_id', 'integer', Rule::exists('products', 'id')],
            'batch_id' => ['nullable', 'integer', Rule::exists('batches', 'id')],
            'quantity' => ['required', 'numeric', 'min:0'],
            'min_threshold' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'site_id' => 'site', 'product_id' => 'produit', 'batch_id' => 'lot',
            'quantity' => 'quantité initiale', 'min_threshold' => 'seuil minimum',
        ];
    }
}
