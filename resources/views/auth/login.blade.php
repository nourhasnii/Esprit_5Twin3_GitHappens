<x-guest-layout>
    <div class="bg-white rounded-2xl shadow-xl shadow-ink/5 border border-ink/5 p-6 sm:p-8 md:p-10 lg:p-12">
        <div class="mb-8">
            <h1 class="font-fraunces font-bold text-3xl sm:text-4xl text-ink tracking-tight mb-2">Connexion</h1>
            <p class="text-ink/60 text-sm sm:text-base">Bienvenue de retour. Accédez à votre tableau de traçabilité.</p>
        </div>

        <x-auth-session-status class="mb-6" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5 sm:space-y-6">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Adresse email')" />
                <x-text-input id="email" class="block mt-1 w-full px-4 py-3" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="vous@exemple.com" />
                @error('email')<p class="field-validation-error mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-input-label for="password" :value="__('Mot de passe')" />
                <div x-data="{ visible: false }" class="relative mt-1">
                    <x-text-input id="password" class="block w-full px-4 py-3 pr-12"
                                    type="password"
                                    x-bind:type="visible ? 'text' : 'password'"
                                    name="password"
                                    required autocomplete="current-password"
                                    placeholder="••••••••" />
                    <button type="button" aria-label="Afficher le mot de passe"
                            x-bind:aria-label="visible ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                            x-bind:aria-pressed="visible"
                            x-on:click="visible = ! visible"
                            class="absolute right-0 inline-flex items-center justify-center rounded-r-lg px-3 text-ink/50 transition hover:text-forest focus:outline-none focus:ring-2 focus:ring-inset focus:ring-amber-warm/40 dark:text-white/55 dark:hover:text-emerald-300" style="top: 50%; transform: translateY(-50%);">
                        <svg x-cloak x-show="! visible" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-cloak x-show="visible" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 3l18 18M10.6 6.2A10.8 10.8 0 0 1 12 6c6 0 9.5 6 9.5 6a15.8 15.8 0 0 1-3.1 3.7M6.2 6.2C3.8 7.7 2.5 12 2.5 12s3.5 6 9.5 6a10.8 10.8 0 0 0 3.1-.5"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                    </button>
                </div>
                @error('password')<p class="field-validation-error mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                    <div class="relative">
                        <input id="remember_me" type="checkbox" class="sr-only peer" name="remember" @checked(old('remember'))>
                        <div class="w-5 h-5 border-2 border-ink/20 rounded-md peer-checked:bg-forest peer-checked:border-forest transition-all duration-150 group-hover:border-forest/50"></div>
                        <svg class="w-3 h-3 text-white absolute top-1 left-1 opacity-0 peer-checked:opacity-100 transition-opacity duration-150 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <path d="M20 6L9 17L4 12"/>
                        </svg>
                    </div>
                    <span class="ms-2.5 text-sm text-ink/60 group-hover:text-ink/80 transition-colors">{{ __('Se souvenir de moi') }}</span>
                </label>
            </div>

            <x-primary-button class="w-full py-3 text-base justify-center">
                {{ __('Se connecter') }}
            </x-primary-button>

            <div class="flex flex-col items-center gap-3 pt-2">
                @if (Route::has('password.request'))
                    <a class="text-sm text-ink/50 hover:text-amber-warm rounded-md focus:outline-none focus:ring-2 focus:ring-amber-warm/30 focus:ring-offset-2 transition-colors" href="{{ route('password.request') }}">
                        {{ __('Mot de passe oublié ?') }}
                    </a>
                @endif

                <div class="w-full h-px bg-ink/5 my-1"></div>

                @if (Route::has('register'))
                    <p class="text-sm text-ink/60">
                        Pas encore de compte ?
                        <a class="font-semibold text-forest hover:text-forest/80 underline decoration-1 underline-offset-4 hover:underline-offset-2 transition-all" href="{{ route('register') }}">
                            {{ __("S'inscrire") }}
                        </a>
                    </p>
                @endif
            </div>
        </form>
    </div>
</x-guest-layout>
