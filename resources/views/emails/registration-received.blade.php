@component('emails.layout', ['title' => 'Registration received'])
<p>Hello {{ $user->name }},</p>
<p>Your NutriTrace registration has been received and is waiting for administrator verification.</p>
<p>Our team will review your professional account and contact you when a decision is ready.</p>
@endcomponent