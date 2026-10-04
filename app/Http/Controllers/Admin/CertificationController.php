<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCertificationRequest;
use App\Http\Requests\Admin\UpdateCertificationRequest;
use App\Jobs\AnalyzeCertificationIntelligenceJob;
use App\Models\Certification;
use App\Models\CertificationAIAnalysis;
use App\Models\Product;
use App\Services\CertificationAIService;
use App\Services\CertificationIntelligenceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function intelligence(
        CertificationIntelligenceService $intelligenceService,
    ) {
        $this->authorize('manage_products', Product::class);

        $intelligence = $intelligenceService->generate();
        $latestSaved = CertificationAIAnalysis::latestSaved();
        $latestAttempt = CertificationAIAnalysis::latestAttempt();
        $history = CertificationAIAnalysis::query()
            ->latest('created_at')
            ->limit(15)
            ->get();

        return view('admin.certifications.intelligence', [
            'intelligence' => $intelligence,
            'ai_analysis' => $latestSaved,
            'used_ai_cache' => false,
            'analysis_status' => $latestAttempt?->status,
            'history' => $history,
        ]);
    }

    public function analyze(
        Request $request,
        CertificationIntelligenceService $intelligenceService,
    ) {
        $this->authorize('manage_products', Product::class);

        if ($request->isMethod('get')) {
            return redirect()->route('admin.certifications.intelligence');
        }

        $request->validate([
            'refresh' => ['nullable', 'boolean'],
        ]);

        $deterministicData = $intelligenceService->generate();
        $analysis = CertificationAIAnalysis::query()->create([
            'summary' => 'Certification AI analysis is pending.',
            'risk_explanation' => 'Deterministic risk metrics remain the source of truth while AI analysis runs.',
            'key_insights' => [],
            'priority_actions' => [],
            'business_impact' => 'AI business impact will be available when analysis completes.',
            'compliance_score' => (int) round((float) ($deterministicData['compliance']['score'] ?? 0)),
            'risk_score' => (int) round((float) ($deterministicData['risk']['score'] ?? 0)),
            'risk_level' => $deterministicData['risk']['level'] ?? null,
            'compliance_level' => $deterministicData['compliance']['level'] ?? null,
            'model' => config('services.ollama.model'),
            'prompt_version' => CertificationAIService::PROMPT_VERSION,
            'status' => 'pending',
        ]);

        AnalyzeCertificationIntelligenceJob::dispatch($analysis, $deterministicData);

        return redirect()->route('admin.certifications.intelligence')
            ->with('success', 'Analyse IA en attente. Les métriques déterministes restent disponibles.');
    }

    public function create()
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'category', 'origin_country', 'origin_region']);

        return view('admin.certifications.create', compact('products'));
    }

    public function store(StoreCertificationRequest $request)
    {
        $validated = $request->validated();
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

        return redirect()->route('admin.certifications.index')->with('success', 'Certification créée avec succès.');
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

    public function update(UpdateCertificationRequest $request, Certification $certification)
    {
        $validated = $request->validated();
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

        return redirect()->route('admin.certifications.index')->with('success', 'Certification mise à jour avec succès.');
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

}
