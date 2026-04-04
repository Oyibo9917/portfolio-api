@component('mail::message')
# Welcome, {{ $user->name }} 🎉

Thanks for signing up.  
We’re excited to have you on board!

@component('mail::button', ['url' => url('/')])
Go to Dashboard
@endcomponent

Regards,<br>
{{ config('app.name') }}
@endcomponent
