<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" x-data="themeManager()" x-init="initTheme()" :class="{ 'dark': isDark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name', 'NutriTrace')); ?> · Workspace</title>
    <script>
        (() => {
            const theme = localStorage.getItem('nutritrace-theme') || 'light';
            const dark = theme === 'dark';
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700;9..144,800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="bg-cream font-inter text-ink antialiased transition-colors dark:bg-[#101815] dark:text-white">
<div x-data="dashboardShell()" x-init="init()" class="min-h-screen relative">
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-ink/40 lg:hidden"></div>

    <div class="relative min-h-screen bg-cream dark:bg-[#101815]">
        <aside
            data-sidebar
            :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full lg:translate-x-0': !sidebarOpen }"
            class="fixed top-4 left-4 bottom-4 z-50 flex w-60 -translate-x-full flex-col overflow-hidden rounded-[20px] bg-gradient-to-b from-[#16281E] to-[#20402E] text-white shadow-[0_20px_60px_-20px_rgba(22,40,30,0.55)] transition-all duration-300 lg:translate-x-0 dark:shadow-black/50"
            aria-label="Sidebar principal"
        >
            <div x-ref="sidebarScroll" @scroll="saveSidebarScroll($event)" class="sidebar-scrollbar-hidden relative h-full flex flex-col overflow-y-auto p-6">
                <button @click="sidebarOpen = false" class="absolute top-4 right-4 z-10 rounded-xl p-1.5 text-white/50 hover:bg-white/5 lg:hidden dark:text-white/50" aria-label="Fermer le menu">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>

                <div class="mb-8 flex w-full flex-col items-center gap-2 pt-1">
                    <div class="relative">
                        <div class="h-20 w-20 rounded-full bg-gradient-to-br from-[#2a5040] to-[#16281E] ring-[3px] ring-[#E3A23C] flex items-center justify-center overflow-hidden shadow-[0_10px_25px_-12px_rgba(227,162,60,0.6)]">
                            <span class="font-fraunces text-2xl font-bold text-[#E3A23C]"><?php echo e(strtoupper(mb_substr(auth()->user()->name, 0, 1))); ?></span>
                        </div>
                    </div>
                    <div class="w-full text-center">
                        <p class="text-sm font-semibold text-white truncate"><?php echo e(auth()->user()->name); ?></p>
                        <p class="mt-0.5 text-[11px] text-white/50 truncate"><?php echo e(auth()->user()->email); ?></p>
                    </div>
                </div>

                <nav class="flex-1 space-y-2 pr-1">
                    <div>
                        <ul class="space-y-1">
                            <li>
                                    <a href="<?php echo e(route('dashboard')); ?>" class="<?php echo e(request()->routeIs('dashboard') ? 'bg-[#E3A23C] text-[#16281E] font-semibold' : 'text-white/70 hover:bg-white/5'); ?> group flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition-colors" @click="sidebarOpen = false">
                                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <?php if(auth()->user()->can('manage_products')): ?>
                        <div>
                            <ul class="space-y-1">
                                <li>
                                    <a href="<?php echo e(route('admin.products.index')); ?>" class="<?php echo e(request()->routeIs('admin.products.*') ? 'bg-[#E3A23C] text-[#16281E] font-semibold' : 'text-white/70 hover:bg-white/5'); ?> group flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition-colors" @click="sidebarOpen = false">
                                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 3c2 3-1 5-3 6 2 2 6 3 3 9-3-2-5-5-6-5-1 2-2 5-5 6 1-3 0-7 3-9-3-2-5-4-3-6 3 1 5 4 5 4 0-3 1-6 6-5Z"/></svg>
                                        <span>Products</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('admin.batches.index')); ?>" class="<?php echo e(request()->routeIs('admin.batches.*') && ! request()->routeIs('admin.batches.traceability') ? 'bg-[#E3A23C] text-[#16281E] font-semibold' : 'text-white/70 hover:bg-white/5'); ?> group flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition-colors" @click="sidebarOpen = false">
                                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M4 8h16v11H4zM4 8l3-4h10l3 4M9 12h6"/></svg>
                                        <span>Batches / Lots</span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <ul class="space-y-1">
                                <li>
                                    <a href="<?php echo e(route('admin.certifications.index')); ?>" class="<?php echo e(request()->routeIs('admin.certifications.*') ? 'bg-[#E3A23C] text-[#16281E] font-semibold' : 'text-white/70 hover:bg-white/5'); ?> group flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition-colors" @click="sidebarOpen = false">
                                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 2l3 3 4-1 1 4 3 3-3 3-1 4-4-1-3 3-3-3-4 1-1-4-3-3 3-3 4 1 1-4z"/><path d="m9 12 2 2 4-4"/></svg>
                                        <span>Certifications</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('account.verification.edit')); ?>" class="<?php echo e(request()->routeIs('account.verification.*') ? 'bg-[#E3A23C] text-[#16281E] font-semibold' : 'text-white/70 hover:bg-white/5'); ?> group flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition-colors" @click="sidebarOpen = false">
                                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                                        <span>Verification</span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <ul class="space-y-1">
                                <li>
                                    <a href="<?php echo e(route('admin.events.index')); ?>" class="<?php echo e(request()->routeIs('admin.events.*') || request()->routeIs('admin.batches.traceability') ? 'bg-[#E3A23C] text-[#16281E] font-semibold' : 'text-white/70 hover:bg-white/5'); ?> group flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition-colors" @click="sidebarOpen = false">
                                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="12" r="3"/><circle cx="4" cy="12" r="1.5"/><circle cx="20" cy="12" r="1.5"/><circle cx="12" cy="4" r="1.5"/><circle cx="12" cy="20" r="1.5"/><path d="M5.5 12H9m6 0h3.5M12 5.5V9m0 6v3.5"/></svg>
                                        <span>Traceability</span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <?php if(auth()->user()->can('manage_users')): ?>
                            <div>
                                <ul class="space-y-1">
                                    <li>
                                        <a href="<?php echo e(route('admin.users.index')); ?>" class="<?php echo e(request()->routeIs('admin.users.*') ? 'bg-[#E3A23C] text-[#16281E] font-semibold' : 'text-white/70 hover:bg-white/5'); ?> group flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition-colors" @click="sidebarOpen = false">
                                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                            <span>Users</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('admin.verification.index')); ?>" class="<?php echo e(request()->routeIs('admin.verification.*') ? 'bg-[#E3A23C] text-[#16281E] font-semibold' : 'text-white/70 hover:bg-white/5'); ?> group flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition-colors" @click="sidebarOpen = false">
                                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M9 11V6h6v5"/><path d="M4 8h16v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8z"/><path d="m9 15 2 2 4-4"/></svg>
                                            <span>Verification Requests</span>
                                        </a>
                                    </li>
                                    <?php if(app('router')->has('admin.audit.index')): ?>
                                        <li>
                                            <a href="<?php echo e(route('admin.audit.index')); ?>" class="<?php echo e(request()->routeIs('admin.audit.*') ? 'bg-[#E3A23C] text-[#16281E] font-semibold' : 'text-white/70 hover:bg-white/5'); ?> group flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition-colors" @click="sidebarOpen = false">
                                                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M4 4h16v16H4zM8 8h8M8 12h8M8 16h5"/></svg>
                                                <span>Audit Logs</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div>
                        <ul class="space-y-1">
                            <li>
                                <a href="<?php echo e(route('profile.edit')); ?>" class="<?php echo e(request()->routeIs('profile.*') ? 'bg-[#E3A23C] text-[#16281E] font-semibold' : 'text-white/70 hover:bg-white/5'); ?> group flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition-colors" @click="sidebarOpen = false">
                                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                                    <span>Mon profil</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>

                <div class="mt-auto pt-8 space-y-1">
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="w-full" @submit.prevent="if(confirm('Se déconnecter de NutriTrace ?')) { $el.submit(); }">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm text-white/70 hover:bg-white/5 transition-colors" @click="sidebarOpen = false">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="min-h-screen pl-0 transition-all duration-300 lg:pl-[17rem]">
            <header class="sticky top-0 z-30 bg-cream/90 backdrop-blur dark:bg-[#101815]/90 border-b border-ink/5">
                <div data-dashboard-header class="flex h-20 items-center justify-between gap-4 px-6 lg:px-10">
                    <div class="flex min-w-0 items-center gap-3">
                        <button @click="sidebarOpen = true" class="rounded-xl p-2 text-ink/60 hover:bg-white lg:hidden dark:text-white/60" aria-label="Ouvrir le menu">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <div class="min-w-0">
                            <?php echo e($header ?? ''); ?>

                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <?php if(isset($headerActions)): ?>
                            <?php echo e($headerActions); ?>

                        <?php else: ?>
                            <a href="<?php echo e(route('front.products.index')); ?>" target="_blank" rel="noopener" class="hidden rounded-xl px-3 py-2 text-sm font-semibold text-ink/55 transition hover:bg-white sm:inline-flex dark:text-white/60 dark:hover:bg-white/5" title="Catalogue public">
                                Catalogue
                            </a>
                            <div class="inline-flex items-center rounded-full border border-ink/10 bg-white p-1 shadow-sm dark:border-white/10 dark:bg-[#1b2923]" role="group" aria-label="Choisir le thème">
                                <button type="button" @click="theme = 'light'; applyTheme()" :class="theme === 'light' ? 'bg-[#E3A23C] text-[#16281E] shadow-sm' : 'text-ink/50 hover:bg-cream dark:text-white/55 dark:hover:bg-white/5'" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition-colors" :aria-pressed="theme === 'light'">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
                                    Clair
                                </button>
                                <button type="button" @click="theme = 'dark'; applyTheme()" :class="theme === 'dark' ? 'bg-[#16281E] text-white shadow-sm' : 'text-ink/50 hover:bg-cream dark:text-white/55 dark:hover:bg-white/5'" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition-colors" :aria-pressed="theme === 'dark'">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.5 15.5A8.5 8.5 0 0 1 8.5 3.5 8.5 8.5 0 1 0 20.5 15.5Z"/></svg>
                                    Sombre
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <main data-dashboard-main class="min-h-[calc(100vh-5rem)]">
                <?php if(session('success')): ?>
                    <div class="px-6 pt-6 lg:px-10">
                        <div class="max-w-[1440px] mx-auto rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-800 dark:text-emerald-300"><?php echo e(session('success')); ?></div>
                    </div>
                <?php endif; ?>
                <?php if(session('error')): ?>
                    <div class="px-6 pt-6 lg:px-10">
                        <div class="max-w-[1440px] mx-auto rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm font-medium text-rose-800 dark:text-rose-300"><?php echo e(session('error')); ?></div>
                    </div>
                <?php endif; ?>
                <?php if(request()->routeIs('dashboard')): ?>
                    <?php echo e($slot); ?>

                <?php else: ?>
                    <div class="px-6 py-8 lg:px-10 lg:py-10">
                        <?php echo e($slot); ?>

                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>
<script>
    function dashboardShell() {
        return {
            sidebarOpen: false,
            init() {
                this.$nextTick(() => {
                    this.$refs.sidebarScroll.scrollTop = Number(localStorage.getItem('nutritrace-sidebar-scroll') || 0);
                });
            },
            saveSidebarScroll(event) {
                localStorage.setItem('nutritrace-sidebar-scroll', String(event.target.scrollTop));
            },
        };
    }

    function themeManager() {
        return {
            theme: 'light',
            isDark: false,
            initTheme() {
                this.theme = localStorage.getItem('nutritrace-theme') || 'light';
                this.applyTheme();
                },
                applyTheme() {
                this.isDark = this.theme === 'dark';
                document.documentElement.classList.toggle('dark', this.isDark);
                localStorage.setItem('nutritrace-theme', this.theme);
            }
        };
    }
</script>
</body>
</html>
<?php /**PATH C:\Users\mdain\nutritrace\resources\views/components/dashboard-layout.blade.php ENDPATH**/ ?>