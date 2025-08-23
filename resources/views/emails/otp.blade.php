<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your verification code</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111;">
<h2>Your verification code</h2>
<p>Use this code to continue signing in:</p>
<p style="font-size: 24px; letter-spacing: 4px; font-weight: bold;">
    {{ $code }}
</p>
<p>This code expires in {{ $minutes }} minutes. Do not share it with anyone.</p>
</body>
</html>