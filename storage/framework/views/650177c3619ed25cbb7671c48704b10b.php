<?php echo csrf_field(); ?>

<div class="space-y-8">
    <section>
        <div class="mb-5 flex items-start gap-3"><div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-forest/10 text-forest"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h14v16H5z"/><path d="M8 8h8M8 12h5"/><path d="m8 16 2 2 5-5"/></svg></div><div><h3 class="font-fraunces text-xl font-bold text-ink">Product</h3><p class="mt-1 text-sm text-ink/55">Associez la certification à un produit existant.</p></div></div>
        <label for="product_id" class="block text-sm font-semibold text-ink">Product <span class="text-amber-warm">*</span></label>
        <select id="product_id" name="product_id" required class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><option value="">Sélectionner un produit</option><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($product->id); ?>" <?php if((string) old('product_id', $certification->product_id ?? '') === (string) $product->id): echo 'selected'; endif; ?>><?php echo e($product->name); ?><?php echo e($product->category ? ' · ' . $product->category : ''); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
        <?php $__errorArgs = ['product_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </section>

    <section class="border-t border-ink/8 pt-8"><div class="mb-5 flex items-start gap-3"><div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-warm/10 text-amber-warm"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12v18H6z"/><path d="M9 7h6M9 11h6M9 15h3"/></svg></div><div><h3 class="font-fraunces text-xl font-bold text-ink">Certification Details</h3><p class="mt-1 text-sm text-ink/55">Donnez à ce document une identité vérifiable.</p></div></div><div class="grid grid-cols-1 gap-5 sm:grid-cols-2"><div><label for="name" class="block text-sm font-semibold text-ink">Certification name <span class="text-amber-warm">*</span></label><input id="name" name="name" type="text" required placeholder="Agriculture Biologique" value="<?php echo e(old('name', $certification->name ?? '')); ?>" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div><div><label for="certificate_number" class="block text-sm font-semibold text-ink">Certificate number <span class="text-amber-warm">*</span></label><input id="certificate_number" name="certificate_number" type="text" required placeholder="CERT-2026-001" value="<?php echo e(old('certificate_number', $certification->certificate_number ?? '')); ?>" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><?php $__errorArgs = ['certificate_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div><div class="sm:col-span-2"><label for="issuing_organization" class="block text-sm font-semibold text-ink">Issuing organization <span class="text-amber-warm">*</span></label><input id="issuing_organization" name="issuing_organization" type="text" required placeholder="Organization name" value="<?php echo e(old('issuing_organization', $certification->issuing_organization ?? '')); ?>" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><?php $__errorArgs = ['issuing_organization'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div></div></section>

    <section class="border-t border-ink/8 pt-8"><div class="mb-5 flex items-start gap-3"><div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-forest/10 text-forest"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div><div><h3 class="font-fraunces text-xl font-bold text-ink">Validity</h3><p class="mt-1 text-sm text-ink/55">Les dates alimentent automatiquement le statut affiché.</p></div></div><div class="grid grid-cols-1 gap-5 sm:grid-cols-2"><div><label for="issued_at" class="block text-sm font-semibold text-ink">Issued date <span class="text-amber-warm">*</span></label><input id="issued_at" name="issued_at" type="date" required value="<?php echo e(old('issued_at', isset($certification) && $certification->issued_at ? $certification->issued_at->format('Y-m-d') : '')); ?>" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><?php $__errorArgs = ['issued_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div><div><label for="expires_at" class="block text-sm font-semibold text-ink">Expiration date <span class="text-amber-warm">*</span></label><input id="expires_at" name="expires_at" type="date" required value="<?php echo e(old('expires_at', isset($certification) && $certification->expires_at ? $certification->expires_at->format('Y-m-d') : '')); ?>" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><p id="expiration-hint" class="mt-1.5 text-xs text-amber-warm"></p><?php $__errorArgs = ['expires_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div></div></section>

    <section class="border-t border-ink/8 pt-8"><div class="mb-5 flex items-start gap-3"><div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-warm/10 text-amber-warm"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16v16H4z"/><path d="M8 16 11 12l2 2 3-4 3 6"/></svg></div><div><h3 class="font-fraunces text-xl font-bold text-ink">Document</h3><p class="mt-1 text-sm text-ink/55">PDF, JPG, JPEG ou PNG. Taille maximale : 5 MB.</p></div></div><?php if(isset($certification) && $certification->document_path): ?><p class="mb-3 rounded-xl bg-cream px-4 py-3 text-sm text-ink/65">Document actuel : <a class="font-semibold text-forest hover:text-amber-warm" href="<?php echo e(\Illuminate\Support\Facades\Storage::disk('public')->url($certification->document_path)); ?>" target="_blank">Voir le document</a></p><?php endif; ?><label for="document" class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border-2 border-dashed border-ink/15 bg-cream/50 px-4 py-5 transition hover:border-forest/35"><span><span class="block text-sm font-semibold text-ink"><?php echo e(isset($certification) ? 'Replace document' : 'Upload document'); ?></span><span id="document-name" class="mt-1 block text-xs text-ink/50">Aucun fichier sélectionné</span></span><span class="rounded-lg bg-white px-3 py-2 text-xs font-semibold text-forest shadow-sm">Choose file</span><input id="document" name="document" type="file" accept=".pdf,.jpg,.jpeg,.png" class="sr-only"></label><?php $__errorArgs = ['document'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><div id="image-preview" class="mt-4 hidden"><img alt="Document preview" class="max-h-48 rounded-xl border border-ink/10 object-contain"></div></section>

    <section class="border-t border-ink/8 pt-8"><div class="mb-5 flex items-start gap-3"><div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-forest/10 text-forest"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg></div><div><h3 class="font-fraunces text-xl font-bold text-ink">Status & notes</h3><p class="mt-1 text-sm text-ink/55">Conservez le contexte utile à la vérification.</p></div></div><div class="grid grid-cols-1 gap-5 sm:grid-cols-2"><div><label for="status" class="block text-sm font-semibold text-ink">Status <span class="text-amber-warm">*</span></label><select id="status" name="status" required class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><?php $__currentLoopData = ['pending' => 'Pending', 'valid' => 'Valid', 'expiring' => 'Expiring soon', 'expired' => 'Expired']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(old('status', $certification->status ?? 'pending') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div><div><label for="notes" class="block text-sm font-semibold text-ink">Notes</label><textarea id="notes" name="notes" rows="3" placeholder="Optional verification notes" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><?php echo e(old('notes', $certification->notes ?? '')); ?></textarea><?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div></div></section>
</div>

<div class="mt-8 flex flex-col-reverse gap-3 border-t border-ink/8 pt-6 sm:flex-row sm:items-center sm:justify-end"><a href="<?php echo e(route('admin.certifications.index')); ?>" class="rounded-xl px-5 py-3 text-center text-sm font-semibold text-ink/60 transition hover:bg-ink/5 hover:text-ink">Cancel</a><button type="submit" class="rounded-xl bg-forest px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-dark"><?php echo e($submitLabel); ?></button></div>

<?php if (! $__env->hasRenderedOnce('679a3202-6736-4282-8c3d-5c5e76a44924')): $__env->markAsRenderedOnce('679a3202-6736-4282-8c3d-5c5e76a44924'); ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('document');
        const fileName = document.getElementById('document-name');
        const preview = document.getElementById('image-preview');
        const previewImage = preview?.querySelector('img');
        const expiration = document.getElementById('expires_at');
        const hint = document.getElementById('expiration-hint');
        fileInput?.addEventListener('change', () => {
            const file = fileInput.files?.[0];
            if (!file) return;
            fileName.textContent = file.name;
            if (file.type.startsWith('image/') && previewImage) {
                previewImage.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
            }
        });
        expiration?.addEventListener('change', () => {
            if (!expiration.value) return;
            const days = Math.ceil((new window.Date(expiration.value) - new window.Date()) / 86400000);
            hint.textContent = days >= 0 && days <= 30 ? 'Cette certification arrive à échéance dans les 30 jours.' : '';
        });
        expiration?.dispatchEvent(new window.Event('change'));
    });
</script>
<?php endif; ?>
<?php /**PATH C:\Users\mdain\nutritrace\resources\views\admin\certifications\_form.blade.php ENDPATH**/ ?>