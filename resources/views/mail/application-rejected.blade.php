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
        Dear {{ $applicant->full_name }}, we reviewed your Citizen ID application
        (Application ID: <strong style="color: #111827;">{{ $applicant->application_id }}</strong>)
        and unfortunately it could not be approved at this time.
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

    @if (filled($editUrl))
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 16px;">
            <tr>
                <td style="background-color: #e6f2ff; border: 1px solid #cce4ff; border-radius: 10px; padding: 14px 16px;">
                    <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #002d59;">
                        You can correct the items listed in the reason above using the secure link below.
                        Only submit the required corrections, then resubmit your application.
                        This link is personal, expires in {{ \App\Support\ApplicantEditToken::EXPIRY_DAYS }} days, and can only be used once.
                    </p>
                </td>
            </tr>
        </table>

        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 24px;">
            <tr>
                <td align="center">
                    <a href="{{ $editUrl }}"
                        style="display: inline-block; background-color: #004386; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 600; line-height: 1.4; padding: 12px 24px; border-radius: 10px;">
                        Edit Application &amp; Submit Corrections
                    </a>
                </td>
            </tr>
        </table>

        <p style="margin: 0 0 24px; font-size: 12px; line-height: 1.6; color: #6b7280;">
            If the button does not work, copy and paste this link into your browser:<br>
            <a href="{{ $editUrl }}" style="color: #004386; word-break: break-all;">{{ $editUrl }}</a>
        </p>
    @else
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 24px;">
            <tr>
                <td style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px 16px;">
                    <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #374151;">
                        This decision is final for this application. No online edit link has been provided.
                        If you need further assistance, please contact the Municipality of Manolo Fortich.
                    </p>
                </td>
            </tr>
        </table>
    @endif

    <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #6b7280;">
        If you have questions or clarifications, please don't hesitate to contact the Municipality of Manolo Fortich
        thru email at {{ \App\Support\ManoloFortich::SUPPORT_EMAIL }} or call us at {{ \App\Support\ManoloFortich::SUPPORT_PHONE }}.
    </p>
@endsection
