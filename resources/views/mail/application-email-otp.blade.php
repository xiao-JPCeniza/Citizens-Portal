@extends('mail.layout')

@section('title', 'Your Verification Code')

@section('content')
    <p style="margin: 0 0 8px; font-size: 12px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: #004386;">
        Email Verification
    </p>

    <h1 style="margin: 0 0 12px; font-size: 24px; font-weight: 700; line-height: 1.3; color: #111827;">
        Your Verification Code
    </h1>

    <p style="margin: 0 0 28px; font-size: 15px; line-height: 1.6; color: #4b5563;">
        Use the code below to verify your email address and continue your Citizen ID application.
        For your security, this code will expire in <strong style="color: #111827;">10 minutes</strong>.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 28px;">
        <tr>
            <td align="center" style="background-color: #e6f2ff; border: 1px solid #cce4ff; border-radius: 12px; padding: 24px 20px;">
                <p style="margin: 0 0 8px; font-size: 12px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: #004386;">
                    One-Time Code
                </p>
                <p style="margin: 0; font-size: 36px; font-weight: 700; letter-spacing: 0.3em; line-height: 1.2; color: #002d59; font-family: 'Courier New', Courier, monospace;">
                    {{ $code }}
                </p>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 24px;">
        <tr>
            <td style="background-color: #e6f9ef; border: 1px solid #ccf3df; border-radius: 10px; padding: 14px 16px;">
                <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #006a2f;">
                    Enter this code on the email verification page to proceed to the application form.
                </p>
            </td>
        </tr>
    </table>

    <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #6b7280;">
        If you did not request this code, you can safely ignore this email. No changes will be made to your application.
    </p>
@endsection
