
<?php
    $rate = $capacity > 0 ? $used / $capacity : 0;
    $level = $rate >= 0.9 ? 'is-high' : ($rate >= 0.7 ? 'is-mid' : '');
?>
<div class="capacity">
    <div class="rack <?php echo e($level); ?>" role="img" aria-label="Occupation <?php echo e(\App\Support\Fmt::n($rate * 100)); ?> %">
        <span style="width: <?php echo e(min(100, round($rate * 100, 1))); ?>%"></span>
    </div>
    <div class="capacity-legend">
        <span><b><?php echo e(\App\Support\Fmt::n($rate * 100)); ?> %</b> occupé</span>
        <span><b><?php echo e(\App\Support\Fmt::q($used)); ?></b> sur <?php echo e(\App\Support\Fmt::q($capacity)); ?></span>
    </div>
</div>
<?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views/stocks/_rack.blade.php ENDPATH**/ ?>