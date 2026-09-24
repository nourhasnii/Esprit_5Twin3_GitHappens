@component('emails.layout', ['title' => 'Verification update'])
<p>Hello {{ $user->name }},</p>
<p>Your NutriTrace account has not been approved.</p>
<div style="padding:16px;background:#fff1f1;border-left:4px solid #c24141;"><strong>Reason:</strong><br>{{ $reason }}</div>
<p>Review the reason above and contact the NutriTrace team if you believe additional clarification is appropriate.</p>
@endcomponent