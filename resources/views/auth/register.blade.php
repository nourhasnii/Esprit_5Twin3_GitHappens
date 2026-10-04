<x-guest-layout>
    <div x-data="{ role: @js(old('role', 'consommateur')) }" class="w-full rounded-2xl border border-ink/5 bg-white p-6 shadow-xl shadow-ink/5 sm:p-8 md:p-10 dark:border-white/10 dark:bg-[#14201B]">
        <div class="mb-8"><p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">NutriTrace onboarding</p><h1 class="mt-2 font-fraunces text-3xl font-bold text-ink dark:text-white">Create your account</h1><p class="mt-2 text-sm text-ink/60 dark:text-white/60">Choose the workspace that fits your role in the food network.</p></div>
        <form method="POST" action="{{ route('register') }}" class="space-y-5">@csrf
            <div><x-input-label :value="__('What type of account are you creating?')" /><div class="mt-3 grid gap-3 sm:grid-cols-3">
                @foreach([['consommateur', 'Consumer', 'Discover and trace products'], ['producteur', 'Producteur', 'For farms and producers'], ['distributeur', 'Distributor', 'For food businesses']] as [$value, $title, $description])
                    <label class="cursor-pointer"><input type="radio" name="role" value="{{ $value }}" x-model="role" class="sr-only peer"><span class="block rounded-xl border-2 border-ink/10 p-4 transition peer-checked:border-amber-warm peer-checked:bg-amber-warm/5 hover:border-ink/25 dark:border-white/10"><span class="block font-semibold text-sm text-ink dark:text-white">{{ $title }}</span><span class="mt-1 block text-xs leading-5 text-ink/50 dark:text-white/50">{{ $description }}</span></span></label>
                @endforeach
            </div>@error('role')<p class="field-validation-error mt-2 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            <div><x-input-label for="name" :value="__('Full name')" /><x-text-input id="name" class="mt-1 block w-full px-4 py-3" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />@error('name')<p class="field-validation-error mt-2 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            <div><x-input-label for="email" :value="__('Email')" /><x-text-input id="email" class="mt-1 block w-full px-4 py-3" type="email" name="email" :value="old('email')" required autocomplete="username" />@error('email')<p class="field-validation-error mt-2 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            <div x-show="role !== 'consommateur'" x-cloak class="space-y-4 rounded-2xl border border-forest/10 bg-forest/5 p-5 dark:border-emerald-300/15 dark:bg-emerald-300/5">
                <p class="text-sm font-semibold text-forest dark:text-emerald-300">Professional profile</p>
                <div><x-input-label for="organization_name" :value="__('Organization name')" /><x-text-input id="organization_name" class="mt-1 block w-full px-4 py-3" type="text" name="organization_name" :value="old('organization_name')" x-bind:required="role !== 'consommateur'" />@error('organization_name')<p class="field-validation-error mt-2 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><x-input-label for="phone" :value="__('Phone')" /><x-text-input id="phone" class="mt-1 block w-full px-4 py-3" type="text" name="phone" :value="old('phone')" />@error('phone')<p class="field-validation-error mt-2 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                    <div><x-input-label for="professional_identifier" :value="__('Professional identifier')" /><x-text-input id="professional_identifier" class="mt-1 block w-full px-4 py-3" type="text" name="professional_identifier" :value="old('professional_identifier')" />@error('professional_identifier')<p class="field-validation-error mt-2 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                    <div><x-input-label for="country" :value="__('Country')" /><x-text-input id="country" class="mt-1 block w-full px-4 py-3" type="text" name="country" :value="old('country')" />@error('country')<p class="field-validation-error mt-2 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                    <div><x-input-label for="region" :value="__('Region')" /><x-text-input id="region" class="mt-1 block w-full px-4 py-3" type="text" name="region" :value="old('region')" />@error('region')<p class="field-validation-error mt-2 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                </div>
                <div><x-input-label for="address" :value="__('Address')" /><textarea id="address" name="address" rows="3" class="mt-1 block w-full rounded-xl border-ink/10 bg-white text-sm dark:border-white/10 dark:bg-white/5">{{ old('address') }}</textarea>@error('address')<p class="field-validation-error mt-2 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            </div>
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <div x-data="{ visible: false }" class="relative mt-1">
                    <x-text-input id="password" class="block w-full px-4 py-3 pr-12" type="password" x-bind:type="visible ? 'text' : 'password'" name="password" required autocomplete="new-password" />
                    <button type="button" aria-label="Afficher le mot de passe" x-bind:aria-label="visible ? 'Masquer le mot de passe' : 'Afficher le mot de passe'" x-bind:aria-pressed="visible" x-on:click="visible = ! visible" class="absolute right-0 inline-flex items-center justify-center rounded-r-lg px-3 text-ink/50 transition hover:text-forest focus:outline-none focus:ring-2 focus:ring-inset focus:ring-amber-warm/40 dark:text-white/55 dark:hover:text-emerald-300" style="top: 50%; transform: translateY(-50%);">
                        <svg x-cloak x-show="! visible" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-cloak x-show="visible" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 3l18 18M10.6 6.2A10.8 10.8 0 0 1 12 6c6 0 9.5 6 9.5 6a15.8 15.8 0 0 1-3.1 3.7M6.2 6.2C3.8 7.7 2.5 12 2.5 12s3.5 6 9.5 6a10.8 10.8 0 0 0 3.1-.5"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                    </button>
                </div>
                @error('password')<p class="field-validation-error mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                <div x-data="{ visible: false }" class="relative mt-1">
                    <x-text-input id="password_confirmation" class="block w-full px-4 py-3 pr-12" type="password" x-bind:type="visible ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" />
                    <button type="button" aria-label="Afficher le mot de passe" x-bind:aria-label="visible ? 'Masquer le mot de passe' : 'Afficher le mot de passe'" x-bind:aria-pressed="visible" x-on:click="visible = ! visible" class="absolute right-0 inline-flex items-center justify-center rounded-r-lg px-3 text-ink/50 transition hover:text-forest focus:outline-none focus:ring-2 focus:ring-inset focus:ring-amber-warm/40 dark:text-white/55 dark:hover:text-emerald-300" style="top: 50%; transform: translateY(-50%);">
                        <svg x-cloak x-show="! visible" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-cloak x-show="visible" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 3l18 18M10.6 6.2A10.8 10.8 0 0 1 12 6c6 0 9.5 6 9.5 6a15.8 15.8 0 0 1-3.1 3.7M6.2 6.2C3.8 7.7 2.5 12 2.5 12s3.5 6 9.5 6a10.8 10.8 0 0 0 3.1-.5"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                    </button>
                </div>
                @error('password_confirmation')<p class="field-validation-error mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <x-primary-button class="mt-2 w-full justify-center py-3 text-base">{{ __('Create account') }}</x-primary-button>
            <p class="pt-1 text-center text-sm text-ink/60 dark:text-white/60">Already registered? <a class="font-semibold text-forest underline dark:text-emerald-300" href="{{ route('login') }}">Sign in</a></p>
        </form>
    </div>
</x-guest-layout>