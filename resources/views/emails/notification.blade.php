<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            -webkit-text-size-adjust: 100%;
        }
        table {
            border-spacing: 0;
        }
        td {
            padding: 0;
        }
        img {
            border: 0;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f1f5f9;
            padding-top: 32px;
            padding-bottom: 48px;
        }
        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-spacing: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .btn {
            background-color: #991b1b;
            color: #ffffff !important;
            padding: 13px 28px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            display: inline-block;
            letter-spacing: 0.2px;
        }
        .btn:hover {
            background-color: #7f1d1d;
        }
        @media screen and (max-width: 600px) {
            .main {
                width: 94% !important;
            }
            .content-cell {
                padding: 24px 20px !important;
            }
        }
    </style>
</head>
@php
    $logoSrc = null;
    if (isset($message) && method_exists($message, 'embed') && file_exists(public_path('images/lasac_logo.png'))) {
        $logoSrc = $message->embed(public_path('images/lasac_logo.png'));
    } else {
        $logoSrc = asset('images/lasac_logo.png');
    }
@endphp
<body>
    <center class="wrapper">
        <table class="main" width="100%">
            <!-- Top Header -->
            <tr>
                <td style="background-color: #ffffff; padding: 20px 28px; text-align: left; border-bottom: 3px solid #b91c1c;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="vertical-align: middle;">
                                @if(!empty($logoSrc))
                                    <img src="{{ $logoSrc }}" alt="LASAC" style="height: 38px; width: auto; max-width: 220px; display: block; border: 0;" />
                                @else
                                    <span style="font-size: 19px; font-weight: 800; color: #991b1b; letter-spacing: 0.5px;">LASAC HRIS</span>
                                @endif
                                <div style="font-size: 11px; color: #64748b; margin-top: 4px; font-weight: 600; letter-spacing: 0.3px;">
                                    Lipa Archdiocesan Social Action Commission, Inc.
                                </div>
                            </td>
                            <td style="text-align: right; vertical-align: middle;">
                                <span style="background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca; font-size: 11px; font-weight: 700; padding: 5px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block;">
                                    HRIS Notification
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Main Content Area -->
            <tr>
                <td class="content-cell" style="padding: 32px 32px 28px 32px; background-color: #ffffff;">
                    <!-- Greeting -->
                    <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 24px; color: #475569;">
                        Hello <strong>{{ $recipientName }}</strong>,
                    </p>

                    <!-- Title Box -->
                    <div style="background-color: #f8fafc; border-left: 4px solid #991b1b; padding: 14px 18px; border-radius: 0 8px 8px 0; margin-bottom: 20px;">
                        <h2 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; line-height: 22px;">
                            {{ $title }}
                        </h2>
                    </div>

                    <!-- Message Body -->
                    <div style="font-size: 15px; line-height: 25px; color: #334155; margin-bottom: 24px;">
                        {!! nl2br(e($messageBody)) !!}
                    </div>

                    @if(!empty($hasAttachment))
                    <!-- Attachment Banner -->
                    <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 16px; margin-bottom: 24px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td width="30" style="vertical-align: middle; font-size: 22px;">
                                    📎
                                </td>
                                <td style="vertical-align: middle; padding-left: 10px;">
                                    <strong style="font-size: 13px; color: #166534; display: block;">Official PDF Payslip Attached</strong>
                                    <span style="font-size: 12px; color: #15803d;">A complete, printable copy is attached to this email for your personal records.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    @endif

                    <!-- Action Button Redirecting to HRIS -->
                    @if(!empty($actionUrl))
                    <div style="text-align: center; margin: 32px 0 28px 0;">
                        <!--[if mso]>
                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $actionUrl }}" style="height:46px;v-text-anchor:middle;width:240px;" arcsize="15%" strokecolor="#991b1b" fillcolor="#991b1b">
                        <w:anchorlock/>
                        <center style="color:#ffffff;font-family:sans-serif;font-size:15px;font-weight:bold;">{{ $actionText ?? 'Go to LASAC HRIS' }} &rarr;</center>
                        </v:roundrect>
                        <![endif]-->
                        <!--[if !mso]><!-- -->
                        <a href="{{ $actionUrl }}" class="btn" target="_blank">
                            {{ $actionText ?? 'Go to LASAC HRIS' }} &rarr;
                        </a>
                        <!--<![endif]-->
                    </div>

                    <!-- Direct link URL fallback -->
                    <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 12px 14px; margin-top: 24px;">
                        <p style="margin: 0 0 6px 0; font-size: 12px; color: #64748b; font-weight: 600;">
                            Button not working? Copy and paste this link into your browser:
                        </p>
                        <a href="{{ $actionUrl }}" target="_blank" style="font-size: 12px; color: #991b1b; word-break: break-all; line-height: 18px;">
                            {{ $actionUrl }}
                        </a>
                    </div>
                    @endif
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td style="background-color: #f8fafc; padding: 22px 28px; text-align: center; border-top: 1px solid #e2e8f0;">
                    <p style="margin: 0 0 6px 0; font-size: 12px; color: #64748b;">
                        This is an automated notification from <strong>Lipa Archdiocesan Social Action Commission, Inc. (LASAC HRIS)</strong>.
                    </p>
                    <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                        Date: {{ now()->timezone('Asia/Manila')->format('F d, Y h:i A') }} (Philippine Standard Time)
                    </p>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
