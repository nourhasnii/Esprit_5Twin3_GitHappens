@component('emails.layout', ['title' => 'Additional information required'])
<p>Hello {{ $user->name }},</p>
<p>Additional information is required for your NutriTrace account.</p>
<div style="padding:16px;background:#fff8ed;border-left:4px solid #c17817;"><strong>Information requested:</strong><br>{{ $reason }}</div>
<p>Please sign in to NutriTrace and provide the requested information so our team can continue the review.</p>
<p><a href="{{ route('login') }}" style="color:#1f3d2e;font-weight:bold;">Return to NutriTrace</a></p>
@endcomponent