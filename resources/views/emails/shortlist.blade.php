@component('mail::message')
# Notification of Admission

Dear {{$candidateName}},

You have been offered provisional admission to study **{{$programme_name}}** in the **{{$department_name}}** at **WUFEDPOLY**.

To proceed, kindly log in to your account to generate a Remita payment slip for the acceptance fee and print your admission offer letter. After printing your offer, proceed to the **Directorate of Higher Studies** at WUFEDPOLY with your original credentials for physical screening.

@component('mail::button', ['url' => $url])
Pay Acceptance Fee
@endcomponent

Thank you for choosing us, and we look forward to welcoming you.

Best regards,
{{ config('app.name') }}
@endcomponent

