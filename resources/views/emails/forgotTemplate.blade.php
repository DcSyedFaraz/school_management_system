<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Boaz | Forgot Password</title>
</head>
<body>
    <div>
        <h2>Hello {{ $mailData['username'] }}!</h2>
        <p>Please Use The Link Below To Reset Your Password.</p>
        <p>Link: {{ url('/resetPassword') }}/{{ $mailData['token'] }}</p>

        <p>Regards,</p>
        <p>Admin Section.</p>
    </div>
</body>
</html>