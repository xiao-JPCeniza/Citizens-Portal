Your Citizen ID application (Application ID: {{ $applicant->application_id }}) has been rejected.

Reason:
{{ $reason }}
@if (filled($remarks))

Remarks:
{{ $remarks }}
@endif
