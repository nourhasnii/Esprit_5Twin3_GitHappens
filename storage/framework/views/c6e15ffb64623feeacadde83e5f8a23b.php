<?php $__env->startComponent('emails.layout', ['title' => 'Your account has been suspended']); ?>
<p>Hello <?php echo e($user->name); ?>,</p>
<p>Your NutriTrace account has been temporarily suspended by an administrator.</p>
<?php if(filled($reason)): ?>
<div style="padding:16px;background:#fff1f1;border-left:4px solid #c24141;"><strong>Reason:</strong><br><?php echo e($reason); ?></div>
<?php else: ?>
<p>No additional reason was provided by the administrator.</p>
<?php endif; ?>
<p>If you believe this is an error or would like to appeal, please contact the NutriTrace administration team.</p>
<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views\emails\account-suspended.blade.php ENDPATH**/ ?>