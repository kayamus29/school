<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0;padding:24px;background:#f5f7fb;font-family:Arial,sans-serif;color:#1f2937;">
    <div style="max-width:720px;margin:0 auto;background:#ffffff;border-radius:12px;padding:32px;">
        <p style="margin-top:0;color:#6b7280;font-size:14px;">{{ $schoolName }}</p>
        <h2 style="margin:0 0 24px 0;font-size:24px;">{{ $subjectLine }}</h2>
        <p style="margin:0 0 16px 0;">Dear Guardian,</p>
        <div style="line-height:1.7;">
            {!! $messageHtml !!}
        </div>
        <p style="margin:24px 0 0 0;">Student: <strong>{{ $studentName }}</strong></p>
        <p style="margin:8px 0 0 0;">Sent by: {{ $senderName }}</p>
    </div>
</body>
</html>
