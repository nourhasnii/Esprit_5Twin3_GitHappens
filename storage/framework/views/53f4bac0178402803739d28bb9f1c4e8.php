
<?php ($impact = \App\Models\OptimizationRecommendation::impactTotals()); ?>
<?php if($impact['plans'] > 0): ?>
    <section class="impact" aria-label="Impact anti-gaspillage">
        <span class="impact-icon" aria-hidden="true">🌱</span>
        <p>
            <b><?php echo e(\App\Support\Fmt::n($impact['saved_kg'], 1)); ?> kg</b> de nourriture sauvés,
            dont <b><?php echo e(\App\Support\Fmt::n($impact['donated_kg'], 1)); ?> kg</b> donnés, soit environ <b><?php echo e(\App\Support\Fmt::q($impact['meals'])); ?></b> repas offerts,
            et <b><?php echo e(\App\Support\Fmt::n($impact['co2_avoided_kg'], 1)); ?> kg CO₂e</b> évités
            <span class="muted">· <?php echo e($impact['plans']); ?> plan(s) anti-gaspillage appliqué(s)</span>
        </p>
    </section>
<?php endif; ?>
<?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views\partials\impact.blade.php ENDPATH**/ ?>