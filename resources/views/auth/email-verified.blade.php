<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verified</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 100px;
            color: #333;
        }
        .container {
            max-width: 400px;
            margin: auto;
        }
        h1 {
            color: #38a169;
        }
        a {
            text-decoration: none;
            color: #3182ce;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Email verified successfully!</h1>
        <p>You can now close this window or return to the application.</p>
        <p><a href="{{ config('app.frontend_url') }}">Go to App</a></p>
    </div>
</body>
</html>
