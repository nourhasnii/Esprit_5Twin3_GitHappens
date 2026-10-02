@csrf

<div class="space-y-6">
    <div>
        <label for="name" class="block text-sm font-semibold text-ink dark:text-white/85">Nom de la catégorie</label>
        <input id="name" name="name" type="text" value="{{ old('name', $category->name ?? '') }}" required maxlength="255" class="mt-2 block w-full rounded-xl border-ink/10 bg-cream px-4 py-3 text-sm focus:border-amber-warm focus:ring-amber-warm/20 dark:border-white/10 dark:bg-[#16281E] dark:text-white">
        @error('name')<p class="mt-1 text-xs text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="description" class="block text-sm font-semibold text-ink dark:text-white/85">Description <span class="font-normal text-ink/45 dark:text-white/45">(facultatif)</span></label>
        <textarea id="description" name="description" rows="4" class="mt-2 block w-full rounded-xl border-ink/10 bg-cream px-4 py-3 text-sm focus:border-amber-warm focus:ring-amber-warm/20 dark:border-white/10 dark:bg-[#16281E] dark:text-white">{{ old('description', $category->description ?? '') }}</textarea>
        @error('description')<p class="mt-1 text-xs text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
    </div>
    <input type="hidden" name="is_active" value="0">
    <label for="is_active" class="flex items-center gap-3 rounded-xl border border-ink/8 bg-cream/60 p-4 dark:border-white/10 dark:bg-[#16281E]">
        <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $category->is_active ?? true)) class="rounded border-ink/20 text-forest focus:ring-amber-warm/30">
        <span><span class="block text-sm font-semibold text-ink dark:text-white">Catégorie active</span><span class="mt-0.5 block text-xs text-ink/50 dark:text-white/50">Les catégories inactives ne sont pas proposées lors de la création d’un produit.</span></span>
    </label>
    @error('is_active')<p class="-mt-5 text-xs text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
</div>

<div class="mt-8 flex flex-col-reverse gap-3 border-t border-ink/8 pt-6 sm:flex-row sm:items-center sm:justify-end dark:border-white/10">
    <a href="{{ route('admin.categories.index') }}" class="rounded-xl px-5 py-3 text-center text-sm font-semibold text-ink/60 hover:bg-ink/5 dark:text-white/60 dark:hover:bg-white/5">Annuler</a>
    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-forest px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-forest-dark">{{ $submitLabel }}</button>
</div>