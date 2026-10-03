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
                <div class="relative mt-1">
                    <x-text-input id="password" class="block w-full px-4 py-3 pr-12"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password"
                                    placeholder="••••••••" />
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
