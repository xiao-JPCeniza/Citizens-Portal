Your Citizen ID application (Application ID: {{ $applicant->application_id }}) has been rejected.

Reason:
{{ $reason }}
@if (filled($remarks))

Remarks:
{{ $remarks }}
@endif
@if (filled($editUrl))

You can correct the items listed in the reason above using this secure personal link:
{{ $editUrl }}

This link expires in {{ \App\Support\ApplicantEditToken::EXPIRY_DAYS }} days and can only be used once. Only submit the required corrections, then resubmit your application.
@else

This decision is final for this application. No online edit link has been provided.
@endif
