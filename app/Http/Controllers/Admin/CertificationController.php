<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class CertificationController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = Certification::query();
        $total = (clone $baseQuery)->count();
        $valid = (clone $baseQuery)->withEffectiveStatus(Certification::STATUS_VALID)->count();
        $expiring = (clone $baseQuery)->withEffectiveStatus(Certification::STATUS_EXPIRING)->count();
        $expired = (clone $baseQuery)->withEffectiveStatus(Certification::STATUS_EXPIRED)->count();

        $certifications = Certification::with('product')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . $request->string('search') . '%';
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('name', 'like', $search)
                        ->orWhere('certificate_number', 'like', $search)
                        ->orWhere('issuing_organization', 'like', $search);
                });
            })
            ->when($request->filled('product_id'), fn ($query) => $query->where('product_id', $request->integer('product_id')))
            ->when($request->filled('status'), fn ($query) => $query->withEffectiveStatus((string) $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name', 'category']);

        return view('admin.certifications.index', compact('certifications', 'products', 'total', 'valid', 'expiring', 'expired'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'category', 'origin_country', 'origin_region']);

        return view('admin.certifications.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);
        $documentPath = $request->hasFile('document')
            ? $request->file('document')->store('certifications', 'public')
            : null;

        try {
            Certification::create(array_merge($validated, [
                'document_path' => $documentPath,
                'status' => Certification::calculateStatus($validated['expires_at'], $validated['status']),
            ]));
        } catch (Throwable $exception) {
            if ($documentPath) {
                Storage::disk('public')->delete($documentPath);
            }

            throw $exception;
        }

        return redirect()->route('admin.certifications.index')->with('success', 'Certification added successfully.');
    }

    public function show(Certification $certification)
    {
        $certification->load('product');

        return view('admin.certifications.show', compact('certification'));
    }

    public function edit(Certification $certification)
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'category', 'origin_country', 'origin_region']);

        return view('admin.certifications.edit', compact('certification', 'products'));
    }

    public function update(Request $request, Certification $certification)
    {
        $validated = $this->validatedData($request, $certification);
        $oldDocument = $certification->document_path;
        $newDocument = $request->hasFile('document')
            ? $request->file('document')->store('certifications', 'public')
            : null;

        try {
            $certification->update(array_merge($validated, [
                'document_path' => $newDocument ?? $oldDocument,
                'status' => Certification::calculateStatus($validated['expires_at'], $validated['status']),
            ]));
        } catch (Throwable $exception) {
            if ($newDocument) {
                Storage::disk('public')->delete($newDocument);
            }

            throw $exception;
        }

        if ($newDocument && $oldDocument) {
            Storage::disk('public')->delete($oldDocument);
        }

        return redirect()->route('admin.certifications.index')->with('success', 'Certification updated successfully.');
    }

    public function destroy(Certification $certification)
    {
        $documentPath = $certification->document_path;
        $certification->delete();

        if ($documentPath) {
            Storage::disk('public')->delete($documentPath);
        }

        return redirect()->route('admin.certifications.index')->with('success', 'Certification deleted successfully.');
    }

    private function validatedData(Request $request, ?Certification $certification = null): array
    {
        return $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'name' => ['required', 'string', 'max:255'],
            'certificate_number' => ['required', 'string', 'max:255', Rule::unique('certifications', 'certificate_number')->ignore($certification?->id)],
            'issuing_organization' => ['required', 'string', 'max:255'],
            'issued_at' => ['required', 'date'],
            'expires_at' => ['required', 'date', 'after_or_equal:issued_at'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'status' => ['required', Rule::in(Certification::STATUSES)],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
