@csrf

<div class="space-y-8">
    <section>
        <div class="mb-5 flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-forest/10 text-forest">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h14v16H5z"/><path d="M8 8h8M8 12h5"/><path d="m8 16 2 2 5-5"/></svg>
            </div>
            <div>
                <h3 class="font-fraunces text-xl font-bold text-ink">Produit</h3>
                <p class="mt-1 text-sm text-ink/55">Associez la certification à un produit existant.</p>
            </div>
        </div>
        <label for="product_id" class="block text-sm font-semibold text-ink">Produit <span class="text-amber-warm">*</span></label>
        <select id="product_id" name="product_id" required class="mt-2 block w-full rounded-xl {{ $errors->has('product_id') ? 'border-red-400' : 'border-ink/15' }} bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20">
            <option value="">Sélectionner un produit</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}" @selected((string) old('product_id', $certification->product_id ?? '') === (string) $product->id)>{{ $product->name }}{{ $product->category ? ' · ' . $product->category : '' }}</option>
            @endforeach
        </select>
        @error('product_id')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </section>

    <section class="border-t border-ink/8 pt-8">
        <div class="mb-5 flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-warm/10 text-amber-warm">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12v18H6z"/><path d="M9 7h6M9 11h6M9 15h3"/></svg>
            </div>
            <div>
                <h3 class="font-fraunces text-xl font-bold text-ink">Détails de la certification</h3>
                <p class="mt-1 text-sm text-ink/55">Donnez à ce document une identité vérifiable.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label for="name" class="block text-sm font-semibold text-ink">Nom de la certification <span class="text-amber-warm">*</span></label>
                <input id="name" name="name" type="text" required minlength="3" maxlength="120" placeholder="Agriculture biologique" value="{{ old('name', $certification->name ?? '') }}" class="mt-2 block w-full rounded-xl {{ $errors->has('name') ? 'border-red-400' : 'border-ink/15' }} bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20">
                @error('name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="certificate_number" class="block text-sm font-semibold text-ink">Numéro de certificat <span class="text-amber-warm">*</span></label>
                <input id="certificate_number" name="certificate_number" type="text" required minlength="3" maxlength="50" placeholder="CERT-2026-001" value="{{ old('certificate_number', $certification->certificate_number ?? '') }}" class="mt-2 block w-full rounded-xl {{ $errors->has('certificate_number') ? 'border-red-400' : 'border-ink/15' }} bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20">
                @error('certificate_number')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="issuing_organization" class="block text-sm font-semibold text-ink">Organisme certificateur <span class="text-amber-warm">*</span></label>
                <input id="issuing_organization" name="issuing_organization" type="text" required minlength="2" maxlength="120" placeholder="Nom de l’organisme" value="{{ old('issuing_organization', $certification->issuing_organization ?? '') }}" class="mt-2 block w-full rounded-xl {{ $errors->has('issuing_organization') ? 'border-red-400' : 'border-ink/15' }} bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20">
                @error('issuing_organization')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="border-t border-ink/8 pt-8">
        <div class="mb-5 flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-forest/10 text-forest">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            </div>
            <div>
                <h3 class="font-fraunces text-xl font-bold text-ink">Validité</h3>
                <p class="mt-1 text-sm text-ink/55">Les dates alimentent automatiquement le statut affiché.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label for="issued_at" class="block text-sm font-semibold text-ink">Date de délivrance <span class="text-amber-warm">*</span></label>
                <input id="issued_at" name="issued_at" type="date" required value="{{ old('issued_at', isset($certification) && $certification->issued_at ? $certification->issued_at->format('Y-m-d') : '') }}" class="mt-2 block w-full rounded-xl {{ $errors->has('issued_at') ? 'border-red-400' : 'border-ink/15' }} bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20">
                @error('issued_at')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="expires_at" class="block text-sm font-semibold text-ink">Date d'expiration <span class="text-amber-warm">*</span></label>
                <input id="expires_at" name="expires_at" type="date" required value="{{ old('expires_at', isset($certification) && $certification->expires_at ? $certification->expires_at->format('Y-m-d') : '') }}" class="mt-2 block w-full rounded-xl {{ $errors->has('expires_at') ? 'border-red-400' : 'border-ink/15' }} bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20">
                <p id="expiration-hint" class="mt-1.5 text-xs text-amber-warm"></p>
                <p id="expiration-error" class="mt-1.5 hidden text-sm text-red-600" aria-live="polite">La date d'expiration doit être postérieure à la date de délivrance.</p>
                @error('expires_at')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="border-t border-ink/8 pt-8">
        <div class="mb-5 flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-warm/10 text-amber-warm">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16v16H4z"/><path d="M8 16 11 12l2 2 3-4 3 6"/></svg>
            </div>
            <div>
                <h3 class="font-fraunces text-xl font-bold text-ink">Document</h3>
                <p class="mt-1 text-sm text-ink/55">PDF, JPG, JPEG ou PNG. Taille maximale : 5 MB.</p>
            </div>
        </div>
        @if(isset($certification) && $certification->document_path)
            @php($currentDocumentPath = ltrim($certification->document_path, '/'))
            @if(\Illuminate\Support\Facades\Storage::disk('public')->exists($currentDocumentPath))
                <p class="mb-3 rounded-xl bg-cream px-4 py-3 text-sm text-ink/65">Document actuel : <a class="font-semibold text-forest hover:text-amber-warm" href="{{ asset('storage/certifications-public/' . basename($currentDocumentPath)) }}" target="_blank">Voir le document</a></p>
            @else
                <p class="mb-3 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">Le document actuel est introuvable.</p>
            @endif
        @endif
        <p id="document-reselect-message" class="mb-3 hidden text-sm text-amber-warm" role="status">Veuillez resélectionner le document.</p>
        <label for="document" class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border-2 border-dashed {{ $errors->has('document') ? 'border-red-400' : 'border-ink/15' }} bg-cream/50 px-4 py-5 transition hover:border-forest/35">
            <span>
                <span class="block text-sm font-semibold text-ink">{{ isset($certification) ? 'Remplacer le document' : 'Téléverser un document' }}</span>
                <span id="document-name" class="mt-1 block text-xs text-ink/50">Aucun fichier sélectionné</span>
            </span>
            <span class="rounded-lg bg-white px-3 py-2 text-xs font-semibold text-forest shadow-sm">Choisir un fichier</span>
            <input id="document" name="document" type="file" accept=".pdf,.jpg,.jpeg,.png" class="sr-only">
        </label>
        @error('document')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        <div id="image-preview" class="mt-4 hidden"><img alt="Aperçu du document" class="max-h-48 rounded-xl border border-ink/10 object-contain"></div>
    </section>

    <section class="border-t border-ink/8 pt-8">
        <div class="mb-5 flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-forest/10 text-forest">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
            </div>
            <div>
                <h3 class="font-fraunces text-xl font-bold text-ink">Statut et notes</h3>
                <p class="mt-1 text-sm text-ink/55">Conservez le contexte utile à la vérification.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label for="status" class="block text-sm font-semibold text-ink">Statut <span class="text-amber-warm">*</span></label>
                <select id="status" name="status" required class="mt-2 block w-full rounded-xl {{ $errors->has('status') ? 'border-red-400' : 'border-ink/15' }} bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20">
                    @foreach(['pending' => 'En attente', 'valid' => 'Valide', 'expiring' => 'Expire bientôt', 'expired' => 'Expiré'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $certification->status ?? 'pending') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="notes" class="block text-sm font-semibold text-ink">Notes</label>
                <textarea id="notes" name="notes" rows="3" maxlength="1000" placeholder="Notes de vérification facultatives" class="mt-2 block w-full rounded-xl {{ $errors->has('notes') ? 'border-red-400' : 'border-ink/15' }} bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20">{{ old('notes', $certification->notes ?? '') }}</textarea>
                @error('notes')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>
</div>

<div class="mt-8 flex flex-col-reverse gap-3 border-t border-ink/8 pt-6 sm:flex-row sm:items-center sm:justify-end">
    <a href="{{ route('admin.certifications.index') }}" class="rounded-xl px-5 py-3 text-center text-sm font-semibold text-ink/60 transition hover:bg-ink/5 hover:text-ink">Annuler</a>
    <button type="submit" class="rounded-xl bg-forest px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-dark">{{ $submitLabel }}</button>
</div>

<script>
    (() => {
        const form = document.currentScript.closest('form');
        form.noValidate = true;
        const issuedDate = form.querySelector('#issued_at');
        const expirationDate = form.querySelector('#expires_at');
        const expirationError = form.querySelector('#expiration-error');
        const expirationHint = form.querySelector('#expiration-hint');
        const fileInput = form.querySelector('#document');
        const fileName = form.querySelector('#document-name');
        const preview = form.querySelector('#image-preview');
        const previewImage = preview.querySelector('img');
        const reselectMessage = form.querySelector('#document-reselect-message');
        const documentResubmitKey = 'certification-document-resubmit';
        const hasServerErrors = form.dataset.hasValidationErrors === 'true';

        const updateExpirationError = () => {
            const invalidDates = issuedDate.value && expirationDate.value && expirationDate.value <= issuedDate.value;
            expirationError.classList.toggle('hidden', !invalidDates);
        };

        const updateExpirationHint = () => {
            if (!expirationDate.value) {
                expirationHint.textContent = '';
                return;
            }
            const days = Math.ceil((new window.Date(`${expirationDate.value}T00:00:00`) - new window.Date()) / 86400000);
            expirationHint.textContent = days >= 0 && days <= 30 ? 'Cette certification arrive à échéance dans les 30 jours.' : '';
        };

        expirationDate.addEventListener('change', updateExpirationHint);
        expirationDate.addEventListener('input', updateExpirationHint);
        issuedDate.addEventListener('change', updateExpirationError);
        expirationDate.addEventListener('change', updateExpirationError);
        expirationDate.addEventListener('input', updateExpirationError);

        fileInput.addEventListener('change', () => {
            const file = fileInput.files?.[0];
            if (!file) return;

            fileName.textContent = file.name;
            try {
                window.sessionStorage.setItem(documentResubmitKey, '1');
            } catch {}

            if (file.type.startsWith('image/')) {
                previewImage.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
            } else {
                preview.classList.add('hidden');
            }
        });

        if (hasServerErrors) {
            try {
                if (window.sessionStorage.getItem(documentResubmitKey) === '1') {
                    reselectMessage.classList.remove('hidden');
                }
                window.sessionStorage.removeItem(documentResubmitKey);
            } catch {}
        } else {
            try {
                window.sessionStorage.removeItem(documentResubmitKey);
            } catch {}
        }

        updateExpirationError();
        updateExpirationHint();
    })();
</script>