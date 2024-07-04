@component('mail::message')
# Notification of Admission
{{$candidateName}},

You have  been offered provisional  admission to study  {{$programme_name }} in
 {{$department_name}}  at WUFEDPOLY. Kindly login to your account to generate remita for payment of Acceptance Fees and print your offer. Thanks you

@component('mail::button', ['url' => $url])
Pay Acceptance Fee
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
