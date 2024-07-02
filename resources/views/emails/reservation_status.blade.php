<!DOCTYPE html>
<html>
<head>
    <title>Reservation Status Update</title>
</head>
<body>
    <h1>Dear {{ $customer }},</h1>
    <p>Your reservation status has been updated to: <strong>{{ $status }}</strong>.</p>
    <p>Details of your reservation:</p>
    <ul>
        <li>Date: {{ $date }}</li>
        <li>Time Slot: {{ $time_slot }}</li>
    </ul>
    <p>Thank you for using our service.</p>
</body>
</html>
