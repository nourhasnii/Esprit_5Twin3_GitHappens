@component('emails.layout', ['title' => 'Your account has been suspended'])
<p>Hello {{ $user->name }},</p>
<p>Your NutriTrace account has been temporarily suspended by an administrator.</p>
@if(filled($reason))
<div style="padding:16px;background:#fff1f1;border-left:4px solid #c24141;"><strong>Reason:</strong><br>{{ $reason }}</div>
@else
<p>No additional reason was provided by the administrator.</p>
@endif
<p>If you believe this is an error or would like to appeal, please contact the NutriTrace administration team.</p>
@endcomponent
