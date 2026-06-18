@extends('mail.layout')

@section('title', 'Distribution Event')

@section('content')
    <p style="margin: 0 0 8px; font-size: 12px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: #006a2f;">
        Distribution Event
    </p>

    <h1 style="margin: 0 0 12px; font-size: 24px; font-weight: 700; line-height: 1.3; color: #111827;">
        Hello, {{ $applicant->full_name }}!
    </h1>

    <p style="margin: 0 0 28px; font-size: 15px; line-height: 1.6; color: #4b5563;">
        Your Citizen ID is ready for distribution. Please see the event details below and bring a valid ID when claiming your card.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 28px;">
        <tr>
            <td style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="padding-bottom: 14px;">
                            <p style="margin: 0 0 4px; font-size: 11px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: #6b7280;">When</p>
                            <p style="margin: 0; font-size: 15px; font-weight: 600; line-height: 1.5; color: #111827;">{{ $when }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 14px;">
                            <p style="margin: 0 0 4px; font-size: 11px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: #6b7280;">Where</p>
                            <p style="margin: 0; font-size: 15px; font-weight: 600; line-height: 1.5; color: #111827;">{{ $where }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="margin: 0 0 4px; font-size: 11px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: #6b7280;">What</p>
                            <p style="margin: 0; font-size: 15px; line-height: 1.5; color: #374151;">{{ $what }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if (file_exists($posterPath))
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 24px;">
            <tr>
                <td align="center">
                    <img
                        src="{{ $message->embed($posterPath) }}"
                        alt="Distribution event poster"
                        style="display: block; max-width: 100%; height: auto; border-radius: 12px; border: 1px solid #e5e7eb;"
                    >
                </td>
            </tr>
        </table>
    @endif

    <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #6b7280;">
        A copy of the event poster is also attached to this email. If you have questions, please contact the Municipality of Manolo Fortich.
    </p>
@endsection
