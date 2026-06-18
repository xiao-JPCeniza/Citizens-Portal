@extends('mail.layout')

@section('title', 'Application Received')

@section('content')
    <p style="margin: 0 0 8px; font-size: 12px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: #004386;">
        Application Status
    </p>

    <h1 style="margin: 0 0 12px; font-size: 24px; font-weight: 700; line-height: 1.3; color: #111827;">
        Application Received
    </h1>

    <p style="margin: 0 0 28px; font-size: 15px; line-height: 1.6; color: #4b5563;">
        Thank you, {{ $applicant->full_name }}, for submitting your Citizen ID application.
        We have successfully received your documents and information.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 28px;">
        <tr>
            <td align="center" style="background-color: #e6f2ff; border: 1px solid #cce4ff; border-radius: 12px; padding: 24px 20px;">
                <p style="margin: 0 0 8px; font-size: 12px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: #004386;">
                    Current Status
                </p>
                <p style="margin: 0; font-size: 18px; font-weight: 700; line-height: 1.4; color: #002d59;">
                    Under Verification
                </p>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 24px;">
        <tr>
            <td style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px 16px;">
                <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #374151;">
                    Our team will review your application in the order it was received.
                    Please wait for another email regarding the result of your application.
                </p>
            </td>
        </tr>
    </table>

    <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #6b7280;">
        No further action is needed at this time. We appreciate your patience.
    </p>
@endsection
