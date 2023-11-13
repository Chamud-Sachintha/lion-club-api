<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lion Club - Region Allocation Notification</title>
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

        .allocation-details {
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
        <h1>Region Allocation Notification</h1>
        <p>Dear Context User,</p>

        <p>We are pleased to inform you that you have been allocated to manage the Lion Club activities in the following region:</p>

        <div class="allocation-details">
            <p><strong>Context User:</strong> {{ $details["userName"] }}</p>
            <p><strong>Allocated Region:</strong> {{ $details["region"] }}</p>
        </div>

        <p>You are now responsible for overseeing and coordinating activities within this region. If you have any questions or need further assistance, please feel free to reach out to us.</p>

        <p class="footer"><strong>Best Regards,<br>Lion Club Administration Team</strong></p>
    </div>
</body>

</html>
