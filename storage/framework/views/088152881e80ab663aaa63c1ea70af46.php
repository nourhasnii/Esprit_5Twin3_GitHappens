<?php use \App\Support\Fmt; ?>
<?php use \App\Support\BatchAttributes; ?>
<?php use \App\Services\Optimization\OptimizationEngine; ?>

<?php $__env->startSection('title', 'Trouver une destination'); ?>
<?php $__env->startSection('eyebrow', 'Optimization workspace'); ?>
<?php $__env->startSection('description', 'Choisissez un lot et son site d’origine pour comparer les destinations faisables.'); ?>
<?php $__env->startSection('page-action'); ?><a class="btn btn-ghost" href="<?php echo e(route('optimization.index')); ?>">Retour aux recommandations</a><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div style="display:grid; grid-template-columns: minmax(0, 3fr) minmax(260px, 2fr); gap: 20px; align-items: start;" class="split">
    <form method="POST" action="<?php echo e(route('optimization.store')); ?>" class="panel" id="optim-form">
        <?php echo csrf_field(); ?>
        <div class="form-grid" style="grid-template-columns: 1fr;">
            <div class="field">
                <label for="batch_id">Lot à répartir</label>
                <select id="batch_id" name="batch_id" required>
                    <option value="">Choisir un lot</option>
                    <?php $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $d = BatchAttributes::daysToExpiry($batch); ?>
                        <option value="<?php echo e($batch->id); ?>" <?php if(old('batch_id', $selectedBatch) == $batch->id): echo 'selected'; endif; ?>>
                            <?php echo e(BatchAttributes::code($batch)); ?>, <?php echo e($batch->product?->name); ?><?php if($d !== null): ?>, DLC dans <?php echo e($d); ?> j <?php endif; ?>
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <p class="hint" id="location-hint" aria-live="polite">Les lots rappelés ou périmés ne sont pas proposés.</p>
                <?php $__errorArgs = ['batch_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="field">
                <label for="source_site_id">Site d’origine</label>
                <select id="source_site_id" name="source_site_id" required>
                    <option value="">Choisir un site</option>
                    <?php $__currentLoopData = $sites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $site): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($site->id); ?>" <?php if(old('source_site_id', $selectedSource) == $site->id): echo 'selected'; endif; ?>><?php echo e($site->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['source_site_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="field">
                <label for="quantity">Quantité à envoyer</label>
                <input type="number" id="quantity" name="quantity" value="<?php echo e(old('quantity', $selectedQuantity)); ?>" min="0.01" step="any" required>
                <?php $__errorArgs = ['quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>
        <div class="form-foot">
            <button class="btn btn-primary" type="submit">Calculer la recommandation</button>
        </div>
    </form>

    <aside class="panel">
        <h2>Comment le score est calculé</h2>
        <p class="muted small" style="margin: 6px 0 14px;">Les sites qui ne peuvent pas recevoir le lot (capacité insuffisante, arrivée après la DLC, position inconnue) sont écartés. Les autres reçoivent une note de 0 à 100 sur cinq critères, pondérés ainsi :</p>
        <?php $sum = array_sum($weights) ?: 1; ?>
        <dl class="weights">
            <?php $__currentLoopData = OptimizationEngine::CRITERIA; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div>
                    <dt><?php echo e($label); ?></dt>
                    <dd><span class="bar"><span style="width: <?php echo e(round(($weights[$key] ?? 0) / $sum * 100)); ?>%"></span></span> <?php echo e(round(($weights[$key] ?? 0) / $sum * 100)); ?> %</dd>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </dl>
        <p class="muted small" style="margin-top: 14px;">Si la DLC est dans <?php echo e(config('stock.optimization.urgent_expiry_days')); ?> jours ou moins, la DLC et la proximité pèsent davantage.</p>
    </aside>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .weights { margin: 0; display: grid; gap: 10px; }
    .weights div { display: grid; grid-template-columns: 1fr auto; gap: 4px 12px; align-items: center; }
    .weights dt { font-size: 0.9rem; }
    .weights dd { margin: 0; display: flex; align-items: center; gap: 8px; font-variant-numeric: tabular-nums; font-size: 0.88rem; font-weight: 600; }
    .weights .bar { width: 90px; height: 8px; background: var(--slot); border-radius: 4px; overflow: hidden; display: inline-block; }
    .weights .bar span { display: block; height: 100%; background: var(--pine); }
    @media (max-width: 820px) { .split { grid-template-columns: 1fr !important; } }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    (() => {
        const locations = <?php echo json_encode($locations, 15, 512) ?>;
        const siteNames = <?php echo json_encode($sites->pluck('name', 'id'), 512) ?>;
        const batch = document.getElementById('batch_id');
        const source = document.getElementById('source_site_id');
        const quantity = document.getElementById('quantity');
        const hint = document.getElementById('location-hint');
        const fmt = new Intl.NumberFormat('fr-FR');

        const refresh = (prefill) => {
            const rows = locations[batch.value] || [];
            if (!batch.value) return;
            if (rows.length === 0) {
                hint.textContent = 'Ce lot n’est encore en stock sur aucun site : le transfert ne pourra pas être enregistré automatiquement.';
                return;
            }
            hint.textContent = 'En stock : ' + rows.map((r) => (siteNames[r.site_id] ?? 'Site ' + r.site_id) + ' (' + fmt.format(r.quantity) + ')').join(', ') + '.';
            if (prefill) {
                source.value = rows[0].site_id;
                quantity.value = rows[0].quantity;
            }
        };

        batch.addEventListener('change', () => refresh(true));
        source.addEventListener('change', () => {
            const row = (locations[batch.value] || []).find((r) => String(r.site_id) === source.value);
            if (row) quantity.value = row.quantity;
        });
        refresh(!source.value);
    })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.stock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views/optimization/create.blade.php ENDPATH**/ ?>