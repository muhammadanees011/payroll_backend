<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XEPayroll</title>
    <style>
        body {
            color:#777;
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4; /* Background color for the entire body */
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff; /* Background color for the content */
            border: 1px solid #ddd; /* Border around the content */
            border-radius: 8px; /* Optional: Add rounded corners */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Optional: Add a subtle shadow */
        }
        h4 {
            text-align: center;
        }
        p {
            margin-bottom: 15px;
            text-align: justify;
        }
        strong {
            font-weight: bold;
        }
        footer {
            margin-top: 20px;
            font-size: 0.9em; /* Adjust the font size as needed */
            color: #777;
            text-align: center;
            font-style: italic; /* Add italic style */
        }
        footer p {
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h4 style="color:#235667;">Verify Your Email Address</h4>
        <p>
            Thank you for registering with <strong style="color:#235667 !important;">XEPayroll</strong>! Please enter this confirmation code in the window where you started creating your account.
        </p>
        <h4 style="color:#235667;">{{$verification_code}}</h4>
        <footer>
            <p>
                This is an auto-generated email, Please do not respond to this email.
                <br>
                <br>
                Best Regards,<br>
                XEPayroll Team
            </p>
        </footer>
    </div>
</body>

</html>
