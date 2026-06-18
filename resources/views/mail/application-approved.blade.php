@extends('mail.layout')

@section('title', 'Application Approved')

@section('content')
    <p style="margin: 0 0 8px; font-size: 12px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: #006a2f;">
        Application Status
    </p>

    <h1 style="margin: 0 0 12px; font-size: 24px; font-weight: 700; line-height: 1.3; color: #111827;">
        Congratulations, {{ $applicant->full_name }}!
    </h1>

    <p style="margin: 0 0 28px; font-size: 15px; line-height: 1.6; color: #4b5563;">
        Your Citizen ID application has been <strong style="color: #006a2f;">approved</strong>.
        Our team has reviewed your documents and confirmed that your application meets the requirements.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 28px;">
        <tr>
            <td align="center" style="background-color: #e6f9ef; border: 1px solid #ccf3df; border-radius: 12px; padding: 24px 20px;">
                <p style="margin: 0 0 8px; font-size: 12px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: #006a2f;">
                    Approved
                </p>
                <p style="margin: 0; font-size: 18px; font-weight: 700; line-height: 1.4; color: #004d22;">
                    Your application is now finalized.
                </p>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 24px;">
        <tr>
            <td style="background-color: #e6f2ff; border: 1px solid #cce4ff; border-radius: 10px; padding: 14px 16px;">
                <p style="margin: 0 0 6px; font-size: 13px; font-weight: 600; color: #004386;">
                    What happens next?
                </p>
                <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #002d59;">
                    We will send you another email once the distribution event is scheduled.
                    Please wait for further instructions regarding when and where you can claim your Citizen ID.
                </p>
            </td>
        </tr>
    </table>

    <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #6b7280;">
        No further action is needed at this time. Thank you for applying through the Citizen ID Application Portal.
    </p>
@endsection
