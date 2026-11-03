<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Change la langue de l'interface et revient sur la page courante. */
class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        abort_unless(array_key_exists($locale, config('app.available_locales', [])), 404);

        $request->session()->put('locale', $locale);

        return redirect()->back(fallback: url('/'));
    }
}
