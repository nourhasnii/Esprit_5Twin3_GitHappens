<x-dashboard-layout>
    <x-slot name="header">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">Administration / Users</p>
        <h1 class="mt-1 font-fraunces text-2xl font-semibold text-ink dark:text-white">{{ $user->name }}</h1>
    </x-slot>

    <div class="mx-auto grid max-w-[1100px] gap-6 lg:grid-cols-[1.1fr_.9fr]">
        <section class="space-y-6">
            <div class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#1b2923]">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">Profile</p>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    @foreach([
                        ['Name', $user->name],
                        ['Email', $user->email],
                        ['Role', ucfirst($user->getRoleNames()->first() ?: '—')],
                        ['Registered', $user->created_at?->format('d M Y, H:i')],
                        ['Phone', $user->phone ?: '—'],
                        ['Status', str_replace('_', ' ', ucfirst($user->account_status))],
                    ] as [$label, $value])
                        <div>
                            <p class="text-xs text-ink/45 dark:text-white/45">{{ $label }}</p>
                            <p class="mt-1 font-semibold dark:text-white">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#1b2923]">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">Organization</p>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    @foreach([
                        ['Organization', $user->organization_name ?: '—'],
                        ['Identifier', $user->professional_identifier ?: '—'],
                        ['Location', collect([$user->region, $user->country])->filter()->join(', ') ?: '—'],
                        ['Address', $user->address ?: '—'],
                    ] as [$label, $value])
                        <div>
                            <p class="text-xs text-ink/45 dark:text-white/45">{{ $label }}</p>
                            <p class="mt-1 dark:text-white">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#1b2923]">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">Account controls</p>

                <form method="POST" action="{{ route('admin.users.status', $user) }}" class="mt-4">
                    @csrf
                    <input type="hidden" name="account_status" value="{{ $user->account_status === 'suspended' ? 'active' : 'suspended' }}">
                    <button class="rounded-xl border border-ink/10 px-4 py-2.5 text-sm font-semibold dark:border-white/10 dark:text-white">
                        {{ $user->account_status === 'suspended' ? 'Reactivate account' : 'Suspend account' }}
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#1b2923]">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">Verification</p>
                <p class="mt-3 font-fraunces text-2xl font-semibold dark:text-white">
                    {{ str_replace('_', ' ', ucfirst($user->account_status)) }}
                </p>

                @if($user->verificationRequests->last())
                    <p class="mt-2 text-sm text-ink/55 dark:text-white/55">
                        Submitted {{ $user->verificationRequests->last()->submitted_at?->diffForHumans() }}
                    </p>

                    @if(in_array($user->account_status, ['pending', 'information_required'], true))
                        <a href="{{ route('admin.verification.show', $user->verificationRequests->last()) }}" class="mt-5 inline-flex rounded-xl bg-forest px-4 py-2.5 text-sm font-semibold text-white">
                            Review verification
                        </a>
                    @endif
                @endif
            </div>

            <div class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#1b2923]">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">Previous decisions</p>

                <div class="mt-4 space-y-3">
                    @forelse($user->verificationRequests as $request)
                        <div class="border-b border-ink/8 pb-3 last:border-0 dark:border-white/10">
                            <p class="text-sm font-semibold dark:text-white">
                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                            </p>
                            <p class="text-xs text-ink/50 dark:text-white/50">
                                {{ $request->reviewed_at?->format('d M Y') ?: 'Submitted ' . $request->submitted_at?->format('d M Y') }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-ink/50">No verification requests.</p>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>
</x-dashboard-layout>