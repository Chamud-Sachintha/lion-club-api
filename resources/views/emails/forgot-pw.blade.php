<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lion Club - Activity Evaluation Completed</title>
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

        .activity-details {
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
        <h1>Account Password Reset Request</h1>
        <p>Dear Lion,</p>

        <p>We hope this email finds you well. It appears that a password reset request has been initiated for your Lion Club account. If you did not make this request, please disregard this email, and your account will remain secure.</p>

        <div class="activity-details">
            <p>If you did request a password reset, please use the following temporary password to log in to your account:</p>
            <p><strong>Password reset Code:</strong> {{ $details['code'] }}</p>

    	<p>Once logged in, we highly recommend changing your password immediately to ensure the security of your account.</p>
        </div>

        <div class="comment">
            <p>If you have any questions, please don't hesitate to contact Lion Pani Fonseka. You can reach him through mobile and WhatsApp at 0772930153 or via email at fonsekapani@gmail.com.</p>

            <p>Thank you for your prompt attention to this matter. We value your security and privacy within the Lion Club community.</p>
        </div>
        <p class="footer"><strong>Best Regards,<br>District Club Evaluation Committee</strong></p>
    </div>
</body>
</html>