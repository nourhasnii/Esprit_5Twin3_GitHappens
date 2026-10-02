<?php

namespace App\Http\Requests;

use App\Enums\StockMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sites = Rule::exists('sites', 'id');

        return [
            'type' => ['required', Rule::enum(StockMovementType::class)],
            'product_id' => ['nullable', 'required_without:batch_id', 'integer', Rule::exists('products', 'id')],
            'batch_id' => ['nullable', 'integer', Rule::exists('batches', 'id')],
            'source_site_id' => ['nullable', 'required_if:type,out,transfer', 'integer', $sites],
            'destination_site_id' => ['nullable', 'required_if:type,in,transfer', 'integer', $sites],
            'site_id' => ['nullable', 'required_if:type,adjustment', 'integer', $sites],
            'quantity' => ['nullable', 'required_unless:type,adjustment', 'numeric', 'gt:0'],
            'counted_quantity' => ['nullable', 'required_if:type,adjustment', 'numeric', 'min:0'],
            'moved_at' => ['nullable', 'date', 'before_or_equal:now'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => 'produit', 'batch_id' => 'lot',
            'source_site_id' => 'site d’origine', 'destination_site_id' => 'site de destination',
            'site_id' => 'site', 'quantity' => 'quantité', 'counted_quantity' => 'quantité comptée',
            'moved_at' => 'date du mouvement', 'reason' => 'motif',
        ];
    }
}
