<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invitation</title>
    <style>
        body { margin: 0; padding: 0; background: #f6f7fb; font-family: Arial, sans-serif; color: #111827; }
        .container { max-width: 600px; margin: 24px auto; background: #ffffff; border-radius: 8px; overflow: hidden; }
        .header { padding: 24px; }
        .content { padding: 24px; }
        .button {
            display: inline-block;
            background: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 12px 18px;
            border-radius: 6px;
            margin-top: 12px;
        }
        .footer { font-size: 12px; color: #6b7280; padding: 16px 24px; border-top: 1px solid #eef2f7; }
        .copyright { font-size: 11px; color: #9ca3af; text-align: center; margin-top: 12px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
         <img src="{{ asset('email/logo.png') }}" alt="Woliba" width="120"> 
        <h2 style="margin:0;">Woliba</h2>
    </div>
    <div class="content">
        <h1 style="margin: 0 0 12px 0; font-size: 20px;">
            Hi {{ $invitation->first_name }} {{ $invitation->last_name }},
        </h1>
        <p style="margin: 0 0 16px 0; font-size: 14px;">
            You’ve been invited to join Woliba. Use the magic link below to continue.
        </p>
        <a href="{{ $signedLinkUrl }}" class="button">Register Now</a>
        <p style="margin-top: 16px; font-size: 12px; color: #6b7280;">
            This link will expire soon and can be used only once.
        </p>
    </div>
    <div class="footer">
        If you didn’t request this, you can safely ignore this email.
    </div>
</div>
<p class="copyright">© {{ date('Y') }} Woliba. All rights reserved.</p>
</body>
</html>