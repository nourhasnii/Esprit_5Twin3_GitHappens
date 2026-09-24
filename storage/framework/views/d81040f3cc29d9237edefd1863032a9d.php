<?php
    $eventLabels = ['production' => 'Production', 'processing' => 'Processing', 'transport' => 'Transport', 'storage' => 'Storage', 'distribution' => 'Distribution'];
?>
<?php echo csrf_field(); ?>
<div x-data="{ type: '<?php echo e(old('event_type', $event->event_type ?? 'production')); ?>' }" class="space-y-8">
    <section><div class="mb-5"><p class="text-xs font-bold uppercase tracking-widest text-amber-warm">01 / Context</p><h3 class="mt-1 font-fraunces text-xl font-bold text-ink">Event context</h3><p class="mt-1 text-sm text-ink/55">Place this event in the journey of a batch.</p></div><div class="grid grid-cols-1 gap-5 sm:grid-cols-2"><div class="sm:col-span-2"><label for="batch_id" class="block text-sm font-semibold text-ink">Batch <span class="text-amber-warm">*</span></label><select id="batch_id" name="batch_id" required class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><option value="">Select a batch</option><?php $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batchOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($batchOption->id); ?>" <?php if((string) old('batch_id', $event->batch_id ?? $selectedBatch ?? '') === (string) $batchOption->id): echo 'selected'; endif; ?>><?php echo e($batchOption->lot_number); ?> · <?php echo e($batchOption->product?->name ?? 'Product unavailable'); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><?php $__errorArgs = ['batch_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div><div><label for="event_type" class="block text-sm font-semibold text-ink">Event type <span class="text-amber-warm">*</span></label><select id="event_type" name="event_type" x-model="type" required class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><?php $__currentLoopData = $eventLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>"><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><?php $__errorArgs = ['event_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div><div><label for="event_date" class="block text-sm font-semibold text-ink">Date & time <span class="text-amber-warm">*</span></label><input id="event_date" name="event_date" type="datetime-local" required value="<?php echo e(old('event_date', isset($event) && $event->event_date ? $event->event_date->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i'))); ?>" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><?php $__errorArgs = ['event_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div></div></section>

    <section class="border-t border-ink/8 pt-8"><div class="mb-5"><p class="text-xs font-bold uppercase tracking-widest text-amber-warm">02 / Place & actor</p><h3 class="mt-1 font-fraunces text-xl font-bold text-ink">Where it happened</h3></div><div class="grid grid-cols-1 gap-5 sm:grid-cols-2"><div class="sm:col-span-2"><label for="location" class="block text-sm font-semibold text-ink">Location <span class="text-amber-warm">*</span></label><input id="location" name="location" type="text" required placeholder="Farm El Amal, Nabeul" value="<?php echo e(old('location', $event->location ?? '')); ?>" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><?php $__errorArgs = ['location'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div><div><label for="actor_id" class="block text-sm font-semibold text-ink">Actor</label><select id="actor_id" name="actor_id" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><option value="">No registered actor</option><?php $__currentLoopData = $actors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $actor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($actor->id); ?>" <?php if((string) old('actor_id', $event->actor_id ?? '') === (string) $actor->id): echo 'selected'; endif; ?>><?php echo e($actor->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><?php $__errorArgs = ['actor_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div><div><label for="latitude" class="block text-sm font-semibold text-ink">Latitude <span class="font-normal text-ink/45">(optional)</span></label><input id="latitude" name="latitude" type="number" step="0.000001" min="-90" max="90" value="<?php echo e(old('latitude', $event->latitude ?? '')); ?>" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"></div><div><label for="longitude" class="block text-sm font-semibold text-ink">Longitude <span class="font-normal text-ink/45">(optional)</span></label><input id="longitude" name="longitude" type="number" step="0.000001" min="-180" max="180" value="<?php echo e(old('longitude', $event->longitude ?? '')); ?>" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"></div></div></section>

    <section class="border-t border-ink/8 pt-8"><div class="mb-5"><p class="text-xs font-bold uppercase tracking-widest text-amber-warm">03 / Measurements</p><h3 class="mt-1 font-fraunces text-xl font-bold text-ink">Event-specific data</h3><p class="mt-1 text-sm text-ink/55">Transport metrics appear when that event type is selected.</p></div><div class="grid grid-cols-1 gap-5 sm:grid-cols-2"><div><label for="quantity" class="block text-sm font-semibold text-ink">Quantity <span class="font-normal text-ink/45">(kg or batch unit)</span></label><input id="quantity" name="quantity" type="number" step="0.01" min="0" value="<?php echo e(old('quantity', $event->quantity ?? '')); ?>" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><?php $__errorArgs = ['quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div><div x-show="['transport', 'storage'].includes(type)" x-cloak><label for="temperature" class="block text-sm font-semibold text-ink">Temperature <span class="font-normal text-ink/45">(°C)</span></label><input id="temperature" name="temperature" type="number" step="0.01" value="<?php echo e(old('temperature', $event->temperature ?? '')); ?>" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><?php $__errorArgs = ['temperature'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div><div x-show="type === 'transport'" x-cloak><label for="distance_km" class="block text-sm font-semibold text-ink">Distance <span class="font-normal text-ink/45">(km)</span></label><input id="distance_km" name="distance_km" type="number" step="0.01" min="0" value="<?php echo e(old('distance_km', $event->distance_km ?? '')); ?>" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><?php $__errorArgs = ['distance_km'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div><div x-show="type === 'transport'" x-cloak><label for="carbon_emission" class="block text-sm font-semibold text-ink">Carbon emission <span class="font-normal text-ink/45">(kg CO₂e)</span></label><input id="carbon_emission" name="carbon_emission" type="number" step="0.01" min="0" value="<?php echo e(old('carbon_emission', $event->carbon_emission ?? '')); ?>" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><?php $__errorArgs = ['carbon_emission'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div></div></section>

    <section class="border-t border-ink/8 pt-8"><div class="mb-5"><p class="text-xs font-bold uppercase tracking-widest text-amber-warm">04 / Narrative</p><h3 class="mt-1 font-fraunces text-xl font-bold text-ink">Describe the event</h3></div><label for="description" class="block text-sm font-semibold text-ink">Description</label><textarea id="description" name="description" rows="5" placeholder="What happened at this stage of the journey?" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20"><?php echo e(old('description', $event->description ?? '')); ?></textarea><?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></section>
</div>
<div class="mt-8 flex flex-col-reverse gap-3 border-t border-ink/8 pt-6 sm:flex-row sm:justify-end"><a href="<?php echo e(route('admin.events.index')); ?>" class="rounded-xl px-5 py-3 text-center text-sm font-semibold text-ink/60 hover:bg-ink/5">Cancel</a><button type="submit" class="rounded-xl bg-forest px-5 py-3 text-sm font-semibold text-white hover:bg-forest-dark"><?php echo e($submitLabel); ?></button></div>
<?php /**PATH C:\Users\mdain\nutritrace\resources\views\admin\events\_form.blade.php ENDPATH**/ ?>