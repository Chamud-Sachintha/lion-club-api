<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Lion Club</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 20px;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #000000;
        }

        p {
            color: #555;
        }

        .credentials {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            background-color: #3498db;
            color: #fff;
            border-radius: 5px;
        }

        .support {
            margin-top: 20px;
            color: #888;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Welcome to Lion Club!</h1>
        <p>We are delighted to have you on board. Your journey with us begins with accessing your personalized dashboard, where you can explore and engage with all the exciting features and updates.</p>

        <div class="credentials">
            <p><strong>Your Login Information:</strong></p>
            <ul>
                <li><strong>User Role:</strong> {{ $details['userRole'] }}</li>
                <li><strong>Username:</strong> {{ $details['userName'] }}</li>
                <li><strong>Temporary Password:</strong> {{ $details['tempPass'] }}</li>
                <li><strong>Login Link:</strong> <a href="https://dashboard.lions306a2.lk/" target="_blank">https://dashboard.lions306a2.lk/</a></li>
            </ul>

            <p><strong>Getting Started:</strong></p>
            <p>To access your account for the first time, simply click on the provided login link and use the above credentials.</p>

            <p><strong>Important Security Information:</strong></p>
            <ul>
                <li>Please note that this temporary password is valid for your first login only. Upon logging in, you will be prompted to change your password to something more secure and memorable.</li>
                <li>Ensure that you keep your login credentials confidential.</li>
                <li>If you ever forget your password, you can use the "Forgot Password" option on the login page to reset it.</li>
            </ul>
        </div>

        <p class="support">Should you encounter any issues or have questions, our support team is here to assist you. Feel free to reach out to <a href="mailto:support@lions306a2.lk" style="color: #3498db; text-decoration: none;">support@lions306a2.lk</a> for prompt assistance.</p>

        <p>Thank you for being part of the Lion Club community. We look forward to your active participation and hope you find the dashboard a valuable resource.</p>

        <p class="footer"><strong>Best Regards,<br>District Club Evaluation Committee</strong></p>
    </div>
</body>

</html>
