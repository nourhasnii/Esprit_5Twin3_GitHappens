@component('emails.layout', ['title' => 'Your account has been reactivated'])
<p>Hello {{ $user->name }},</p>
<p>Great news: your NutriTrace account has been reactivated by an administrator.</p>
<p>You can now sign in and resume using your workspace normally.</p>
<p><a href="{{ route('login') }}" style="display:inline-block;background:#1f3d2e;color:#fff;padding:12px 18px;border-radius:8px;text-decoration:none;font-weight:bold;">Sign in to NutriTrace</a></p>
@endcomponent
