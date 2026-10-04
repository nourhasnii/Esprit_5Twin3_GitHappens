<?php

namespace App\Http\Requests\Admin;

use App\Models\Certification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCertificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $certification = $this->route('certification');

        return [
            'product_id' => ['required', 'exists:products,id'],
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'certificate_number' => ['required', 'string', 'min:3', 'max:50', Rule::unique('certifications', 'certificate_number')->ignore($certification instanceof Certification ? $certification : null)],
            'issuing_organization' => ['required', 'string', 'min:2', 'max:120'],
            'issued_at' => ['required', 'date'],
            'expires_at' => ['required', 'date', 'after:issued_at'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'status' => ['required', Rule::in(Certification::STATUSES)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Veuillez sélectionner un produit.',
            'product_id.exists' => 'Le produit sélectionné est introuvable.',
            'name.required' => 'Le nom de la certification est obligatoire.',
            'name.string' => 'Le nom de la certification doit être une chaîne de caractères.',
            'name.min' => 'Le nom de la certification doit contenir au moins 3 caractères.',
            'name.max' => 'Le nom de la certification ne peut pas dépasser 120 caractères.',
            'certificate_number.required' => 'Le numéro de certificat est obligatoire.',
            'certificate_number.string' => 'Le numéro de certificat doit être une chaîne de caractères.',
            'certificate_number.min' => 'Le numéro de certificat doit contenir au moins 3 caractères.',
            'certificate_number.max' => 'Le numéro de certificat ne peut pas dépasser 50 caractères.',
            'certificate_number.unique' => 'Ce numéro de certificat est déjà utilisé.',
            'issuing_organization.required' => 'L’organisme certificateur est obligatoire.',
            'issuing_organization.string' => 'L’organisme certificateur doit être une chaîne de caractères.',
            'issuing_organization.min' => 'L’organisme certificateur doit contenir au moins 2 caractères.',
            'issuing_organization.max' => 'L’organisme certificateur ne peut pas dépasser 120 caractères.',
            'issued_at.required' => 'La date de délivrance est obligatoire.',
            'issued_at.date' => 'La date de délivrance doit être une date valide.',
            'expires_at.required' => 'La date d’expiration est obligatoire.',
            'expires_at.date' => 'La date d’expiration doit être une date valide.',
            'expires_at.after' => 'La date d’expiration doit être postérieure à la date de délivrance.',
            'document.file' => 'Le document sélectionné est invalide.',
            'document.mimes' => 'Le document doit être au format PDF, JPG, JPEG ou PNG.',
            'document.max' => 'Le document ne doit pas dépasser 5 Mo.',
            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Le statut sélectionné est invalide.',
            'notes.string' => 'Les notes doivent être une chaîne de caractères.',
            'notes.max' => 'Les notes ne peuvent pas dépasser 1000 caractères.',
        ];
    }
}