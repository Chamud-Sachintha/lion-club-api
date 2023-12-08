<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lion Club - New Activity Submission</title>
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
        <h1>New Activity Submission</h1>
        <p>Dear Lion,</p>

        <p>We would like to inform you that a new activity has been submitted to Lion Club for evaluation. The details of the submitted activity are as follows:</p>

        <div class="activity-details">
            <p><strong>Activity Code:</strong> {{ $details['activityCode'] }}</p>
            <p><strong>Activity Name:</strong>{{ $details['activityName'] }}</p>
            <p><strong>Submitter Name:</strong> {{ $details['submitBy'] }}</p>
            <p><strong>Value:</strong> {{ $details['value'] }}</p>
            <p><strong>Date of Activity:</strong>{{ $details['dateOfActivity'] }}</p>
        </div>

        <p>
            Kindly note that you will be notified upon review of your activity and notified to you. 
        </p>

        <p>
            If you have any questions, please don't hesitate to contact Lion Pani Fonseka. You can reach him through mobile and WhatsApp at 0772930153 or via email at fonsekapani@gmail.com.
        </p>

        <p class="footer"><strong>Best Regards,<br>District Club Evaluation Committee</strong></p>
    </div>
</body>

</html>
