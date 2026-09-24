<?php $__env->startComponent('emails.layout', ['title' => 'Your account has been reactivated']); ?>
<p>Hello <?php echo e($user->name); ?>,</p>
<p>Great news: your NutriTrace account has been reactivated by an administrator.</p>
<p>You can now sign in and resume using your workspace normally.</p>
<p><a href="<?php echo e(route('login')); ?>" style="display:inline-block;background:#1f3d2e;color:#fff;padding:12px 18px;border-radius:8px;text-decoration:none;font-weight:bold;">Sign in to NutriTrace</a></p>
<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\Users\mdain\nutritrace\resources\views\emails\account-reactivated.blade.php ENDPATH**/ ?>