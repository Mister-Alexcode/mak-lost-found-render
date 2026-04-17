<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 20px; }
        .container { max-width: 520px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #16a34a; color: #fff; padding: 20px 24px; }
        .header h1 { margin: 0; font-size: 18px; }
        .body { padding: 24px; color: #374151; line-height: 1.6; }
        .message-box { background: #f0fdf4; border-left: 4px solid #16a34a; padding: 12px 16px; border-radius: 4px; margin: 16px 0; }
        .btn { display: inline-block; background: #16a34a; color: #fff; padding: 10px 24px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 14px; }
        .footer { padding: 16px 24px; text-align: center; color: #9ca3af; font-size: 12px; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>MAK Lost & Found</h1>
        </div>
        <div class="body">
            <p>Hello {{ $userName }},</p>
            <div class="message-box">
                {{ $notificationMessage }}
            </div>
            <p>
                <a href="{{ url('/dashboard') }}" class="btn">View Dashboard</a>
            </p>
            <p style="font-size: 13px; color: #6b7280; margin-top: 20px;">
                You received this email because you enabled email notifications on MAK Lost & Found.
                You can change your notification preferences in your profile settings.
            </p>
        </div>
        <div class="footer">
            Makerere University Lost & Found System
        </div>
    </div>
</body>
</html>
