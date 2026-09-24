<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ActivationRequest;
use App\Services\AccountActivationService;
use App\Services\BrevoMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AccountActivationController extends Controller
{
    public function show(string $token): View
    {
        return view('account.activate', compact('token'));
    }

    public function store(ActivationRequest $request, AccountActivationService $service, BrevoMailService $mail): RedirectResponse
    {
        $service->activate($request->string('token')->toString(), $request->string('password')->toString(), $mail);

        return redirect()->route('login')->with('success', 'Your account is active. You can now sign in.');
    }
}