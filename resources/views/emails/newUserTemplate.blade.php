<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>RMS | New User</title>
</head>

<body>
    <div>
        <h2>Hello {{ $mailData['userName'] }}!</h2>
        <p>Welcome to RMS, Your Account has been successfully created, Following are your user credentials:</p>
        <p><b>Email:</b> {{ $mailData['email'] }}</p>
        <p><b>User Name:</b> {{ $mailData['user_name'] }}</p>
        <p><b>Password:</b> {{ $mailData['password'] }}</p>

        <p>You may login using the following link:</p>
        <p><b>Link:</b> {{ url('/') }}</p>

        <p>Regards,</p>
        <p>Admin Section.</p>
    </div>
</body>

</html>
