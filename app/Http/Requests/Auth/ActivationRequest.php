<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class ActivationRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['token' => ['required', 'string'], 'password' => ['required', 'confirmed', Rules\Password::defaults()]]; }
}