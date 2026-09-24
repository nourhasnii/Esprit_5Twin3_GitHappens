<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VerificationDecisionRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('review_verifications') ?? false; }
    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'min:10', 'max:2000']];
    }
}