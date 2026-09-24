<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasRole('admin') && ! $user->isActive()) {
            return response()->view('account.pending', ['user' => $user], 403);
        }

        return $next($request);
    }
}