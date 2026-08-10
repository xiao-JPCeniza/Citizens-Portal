Your Citizen ID application (Application ID: {{ $applicant->application_id }}) has been rejected.

Reason:
{{ $reason }}
@if (filled($remarks))

Remarks:
{{ $remarks }}
@endif
@if (filled($editUrl))

You can update your information and reupload your passport photo using this secure personal link:
{{ $editUrl }}

This link expires in {{ \App\Support\ApplicantEditToken::EXPIRY_DAYS }} days and can only be used once. Please review the reason above and make the necessary corrections before resubmitting.
@else

This decision is final for this application. No online edit link has been provided.
@endif
