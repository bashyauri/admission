@component('mail::message')
# Admission Decision
{{$candidateName}},

Thank you for applying to {{config('app.name')}} for the {{$programme_name}} program. We appreciate the time and effort you invested in your application.

After a thorough review of all applications, we regret to inform you that we are unable to offer you a place in the program at this time. This year, we received an overwhelming number of applications, and the selection process was highly competitive.

Please know that this decision does not diminish your accomplishments and potential. We encourage you to continue pursuing your academic goals and wish you success in your future endeavors.

We appreciate your interest in {{config('app.name')}} and hope that you will consider applying again in the future.

Thank you once again for considering us as part of your educational journey.

Best regards,



Thanks,<br>
{{ config('app.name') }}
@endcomponent
