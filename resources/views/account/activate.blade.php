<x-guest-layout>
    <div class="w-full rounded-2xl border border-ink/5 bg-white p-8 shadow-xl shadow-ink/5 dark:border-white/10 dark:bg-[#14201B]">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">Account activation</p>
        <h1 class="mt-2 font-fraunces text-3xl font-bold text-ink dark:text-white">Welcome to NutriTrace</h1>
        <p class="mt-2 text-sm text-ink/60 dark:text-white/60">Your account has been approved. Create your password to activate it.</p>
        <form method="POST" action="{{ route('account.activation.store') }}" class="mt-8 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <x-input-label for="password" :value="__('New password')" />
                <x-text-input id="password" class="mt-1 block w-full px-4 py-3" type="password" name="password" required autofocus autocomplete="new-password" />
                @error('password')<p class="field-validation-error mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                <x-text-input id="password_confirmation" class="mt-1 block w-full px-4 py-3" type="password" name="password_confirmation" required autocomplete="new-password" />
                @error('password_confirmation')<p class="field-validation-error mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <x-primary-button class="w-full justify-center py-3">Activate account</x-primary-button>
        </form>
    </div>
</x-guest-layout>