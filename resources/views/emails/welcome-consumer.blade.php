@component('emails.layout', ['title' => 'Your account is ready'])
<p>Hello {{ $user->name }},</p>
<p>Welcome to NutriTrace! Your consumer account is ready to use.</p>
<p>You can now browse verified products and explore the traceability information for every item in our catalog.</p>
<p><a href="{{ route('login') }}" style="display:inline-block;background:#1f3d2e;color:#fff;padding:12px 18px;border-radius:8px;text-decoration:none;font-weight:bold;">Sign in to NutriTrace</a></p>
@endcomponent
