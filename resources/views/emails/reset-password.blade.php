<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Your Password</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:40px 0;">
    <tr>
        <td align="center">

            <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.06);">

                <!-- Header / Brand banner -->
                <tr>
                    <td style="background:linear-gradient(to bottom right, #0a2018, #00896a); padding:36px 32px; text-align:center;">
                        <p style="margin:0 0 6px 0; font-size:12px; font-weight:600; letter-spacing:2px; text-transform:uppercase; color:rgba(255,255,255,0.75);">
                            Techstacks
                        </p>
                        <h1 style="margin:0; font-size:26px; font-weight:700; color:#ffffff;">
                            LogiPay
                        </h1>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:40px 32px;">
                        <h2 style="margin:0 0 12px 0; font-size:20px; font-weight:700; color:#1f2937;">
                            Reset your password
                        </h2>
                        <p style="margin:0 0 24px 0; font-size:14px; line-height:1.7; color:#6b7280;">
                            Hi{{ isset($name) ? ' ' . $name : '' }}, we received a request to reset the password for your LogiPay account (<strong style="color:#374151;">{{ $email }}</strong>). Click the button below to choose a new password.
                        </p>

                        <!-- CTA Button -->
                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 28px auto;">
                            <tr>
                                <td align="center" style="border-radius:6px; background-color:#00c896;">
                                    <a href="{{ $resetUrl }}"
                                       target="_blank"
                                       style="display:inline-block; padding:13px 32px; font-size:15px; font-weight:600; color:#ffffff; text-decoration:none; border-radius:6px;">
                                        Reset Password
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 20px 0; font-size:13px; line-height:1.6; color:#9ca3af;">
                            Or copy and paste this link into your browser:
                        </p>
                        <p style="margin:0 0 28px 0; font-size:13px; line-height:1.6; word-break:break-all;">
                            <a href="{{ $resetUrl }}" style="color:#00c896; text-decoration:none;">{{ $resetUrl }}</a>
                        </p>

                        <div style="padding:14px 16px; background-color:#f9fafb; border-left:4px solid #00c896; border-radius:6px; margin-bottom:8px;">
                            <p style="margin:0; font-size:13px; line-height:1.6; color:#6b7280;">
                                This password reset link will expire in <strong style="color:#374151;">{{ $expireMinutes ?? 60 }} minutes</strong>. If you didn't request a password reset, no action is needed — your account is still secure.
                            </p>
                        </div>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:24px 32px; border-top:1px solid #e5e7eb; text-align:center;">
                        <p style="margin:0; font-size:12px; color:#9ca3af;">
                            &copy; {{ date('Y') }} LogiPay &middot; HR Management System
                        </p>
                    </td>
                </tr>

            </table>

            <table role="presentation" width="480" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding:20px 32px; text-align:center;">
                        <p style="margin:0; font-size:12px; color:#9ca3af;">
                            This is an automated message, please do not reply directly to this email.
                        </p>
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>

</body>
</html>