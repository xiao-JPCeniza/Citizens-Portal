@extends('mail.layout')

@section('title', 'Application Rejected')

@section('content')
    <p style="margin: 0 0 8px; font-size: 12px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: #b91c1c;">
        Application Status
    </p>

    <h1 style="margin: 0 0 12px; font-size: 24px; font-weight: 700; line-height: 1.3; color: #111827;">
        Application Not Approved
    </h1>

    <p style="margin: 0 0 28px; font-size: 15px; line-height: 1.6; color: #4b5563;">
        Dear {{ $applicant->full_name }}, we reviewed your Citizen ID application and unfortunately it could not be approved at this time.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 20px;">
        <tr>
            <td style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 14px 16px;">
                <p style="margin: 0 0 6px; font-size: 12px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: #b91c1c;">
                    Reason
                </p>
                <p style="margin: 0; font-size: 15px; line-height: 1.5; color: #7f1d1d;">
                    {{ $reason }}
                </p>
            </td>
        </tr>
    </table>

    @if (filled($remarks))
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 28px;">
            <tr>
                <td style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px 16px;">
                    <p style="margin: 0 0 6px; font-size: 12px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: #6b7280;">
                        Remarks
                    </p>
                    <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #374151;">
                        {{ $remarks }}
                    </p>
                </td>
            </tr>
        </table>
    @endif

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 24px;">
        <tr>
            <td style="background-color: #e6f2ff; border: 1px solid #cce4ff; border-radius: 10px; padding: 14px 16px;">
                <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #002d59;">
                    You may submit a new application if you wish to apply again. Please review the reason above and make the necessary corrections before reapplying.
                </p>
            </td>
        </tr>
    </table>

    <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #6b7280;">
        If you have questions or clarifications, please  don't hesitate to contact the Municipality of Manolo Fortich.
        thru email at mis@manolofortich.gov.ph or call us at 09178068937.
    </p>
@endsection
