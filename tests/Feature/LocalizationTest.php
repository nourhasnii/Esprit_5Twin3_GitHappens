<?php

/*
| Multilingue : français, anglais, arabe (de droite à gauche).
| La suite de tests tourne avec une langue neutre (phpunit.xml) ; ces tests activent les vraies langues.
*/

require_once __DIR__.'/Stocks/StocksTestHelpers.php';

beforeEach(function () {
    $this->withoutVite();
    config(['app.locale' => 'fr', 'app.fallback_locale' => 'fr']);
});

it('propose les trois langues et mémorise le choix en session', function () {
    $this->from('/login')->get(route('locale.switch', 'en'))
        ->assertRedirect('/login')
        ->assertSessionHas('locale', 'en');

    $this->get('/login')->assertOk()
        ->assertSee('<html lang="en" dir="ltr">', false)
        ->assertSee('Log in')
        ->assertSee('Français')->assertSee('English')->assertSee('العربية');
});

it('affiche l’arabe de droite à gauche avec sa police', function () {
    $this->withSession(['locale' => 'ar'])->get('/login')->assertOk()
        ->assertSee('<html lang="ar" dir="rtl">', false)
        ->assertSee('family=Cairo', false)
        ->assertSee('تسجيل الدخول');
});

it('reste en français par défaut', function () {
    $this->get('/login')->assertOk()
        ->assertSee('<html lang="fr" dir="ltr">', false)
        ->assertDontSee('dir="rtl"', false);
});

it('refuse une langue inconnue', function () {
    $this->get('/locale/de')->assertNotFound();
    $this->withSession(['locale' => 'de'])->get('/login')->assertSee('<html lang="fr"', false);
});

it('traduit les pages du module Stocks et les messages de validation', function () {
    $user = stockUser('distributeur');

    $this->actingAs($user)->withSession(['locale' => 'en'])->get(route('shelf-life.index'))
        ->assertOk()->assertSee('Dynamic shelf life')->assertSee('True shelf life of the batches')->assertDontSee('Vraie durée de vie des lots');

    $this->actingAs($user)->withSession(['locale' => 'ar'])->get(route('shelf-life.index'))
        ->assertOk()->assertSee('الصلاحية الديناميكية');

    app()->setLocale('ar');
    expect(__('validation.required', ['attribute' => __('quantité')]))->toBe('الحقل الكمية مطلوب.');
});

it('a une traduction pour chaque texte de l’interface', function () {
    $keys = [];
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views'))) as $file) {
        if (! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }
        preg_match_all("/__\\(\\s*'((?:[^'\\\\]|\\\\.)*)'/", file_get_contents($file->getPathname()), $m);
        foreach ($m[1] as $k) {
            $keys[stripslashes($k)] = true;
        }
        preg_match_all('/__\(\s*"([^"$]+)"/', file_get_contents($file->getPathname()), $m);
        foreach ($m[1] as $k) {
            $keys[$k] = true;
        }
    }
    $ar = json_decode(file_get_contents(lang_path('ar.json')), true, flags: JSON_THROW_ON_ERROR);
    json_decode(file_get_contents(lang_path('en.json')), true, flags: JSON_THROW_ON_ERROR);
    json_decode(file_get_contents(lang_path('fr.json')), true, flags: JSON_THROW_ON_ERROR);

    $technical = fn ($k) => preg_match('/^[a-z0-9-]+$|^x+-x+$|^[a-z]+\.[a-z_.]+$/', $k);
    $missing = array_values(array_filter(array_keys($keys), fn ($k) => ! isset($ar[$k]) && ! $technical($k)));

    expect($missing)->toBe([]);
});
