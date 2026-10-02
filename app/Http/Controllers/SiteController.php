<?php

namespace App\Http\Controllers;

use App\Enums\SiteType;
use App\Http\Requests\SiteRequest;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(): View
    {
        $sites = Site::query()
            ->withSum('stocks', 'quantity')
            ->withCount('stocks')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('sites.index', compact('sites'));
    }

    public function create(): View
    {
        return view('sites.create', ['site' => new Site(['type' => SiteType::Warehouse, 'is_active' => true])]);
    }

    public function store(SiteRequest $request): RedirectResponse
    {
        $site = Site::create($request->validated());

        return redirect()->route('sites.index')->with('success', 'Site « '.$site->name.' » créé.');
    }

    public function edit(Site $site): View
    {
        return view('sites.edit', compact('site'));
    }

    public function update(SiteRequest $request, Site $site): RedirectResponse
    {
        $site->update($request->validated());

        return redirect()->route('sites.index')->with('success', 'Site « '.$site->name.' » mis à jour.');
    }

    public function destroy(Site $site): RedirectResponse
    {
        $hasHistory = $site->stocks()->exists()
            || $site->incomingMovements()->exists()
            || $site->outgoingMovements()->exists();

        if ($hasHistory) {
            return back()->with('error', 'Ce site a un historique de stock : désactivez-le au lieu de le supprimer.');
        }

        $site->delete();

        return redirect()->route('sites.index')->with('success', 'Site supprimé.');
    }
}
