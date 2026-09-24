@component('emails.layout', ['title' => 'Your account has been approved'])
<p>Hello {{ $user->name }},</p>
<p>Your NutriTrace account has been approved.</p>
<p>Use the secure link below to create your password and activate your account. This link expires in 24 hours and can only be used once.</p>
<p><a href="{{ $activationUrl }}" style="display:inline-block;background:#1f3d2e;color:#fff;padding:12px 18px;border-radius:8px;text-decoration:none;font-weight:bold;">Activate your account</a></p>
@endcomponent