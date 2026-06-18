<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Citizen ID Application Portal')</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1f2937; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f3f4f6;">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%;">
                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(90deg, #111827 0%, #00162c 45%, #002d59 100%); border-radius: 16px 16px 0 0; padding: 28px 32px; border-bottom: 1px solid rgba(0, 90, 179, 0.3);">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td>
                                        @include('mail.partials.brand-logo')
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 16px;">
                                        <p style="margin: 0 0 4px; font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #cce4ff;">
                                            Municipality of Manolo Fortich
                                        </p>
                                        <p style="margin: 0; font-size: 20px; font-weight: 700; line-height: 1.3; color: #ffffff;">
                                            Citizen ID Application Portal
                                        </p>
                                        <p style="margin: 6px 0 0; font-size: 13px; color: #cce4ff;">
                                            Province of Bukidnon, Philippines
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="background-color: #ffffff; padding: 36px 32px; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 32px; border-radius: 0 0 16px 16px; border: 1px solid #e5e7eb; border-top: none; text-align: center;">
                            <p style="margin: 0 0 6px; font-size: 12px; line-height: 1.5; color: #6b7280;">
                                This is an automated message from the Citizen ID Application Portal.
                            </p>
                            <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #9ca3af;">
                                &copy; {{ date('Y') }} Municipality of Manolo Fortich. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
