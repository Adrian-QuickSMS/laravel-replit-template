<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leave Notification</title>
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="background: #886CC0; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center;">
        <h2 style="margin: 0;">{{ $subject }}</h2>
    </div>

    <div style="background: #f8f9fa; padding: 20px; border: 1px solid #e9ecef; border-top: none; border-radius: 0 0 8px 8px;">
        <p>A leave request has been <strong>{{ $action }}</strong>.</p>

        <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6; font-weight: bold; width: 40%;">Employee</td>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">{{ $employeeName }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6; font-weight: bold;">Dates</td>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">{{ $startDate }} - {{ $endDate }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6; font-weight: bold;">Duration</td>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">{{ $durationDays }} days</td>
            </tr>
            @if($showLeaveType)
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6; font-weight: bold;">Type</td>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">{{ $leaveType }}</td>
            </tr>
            @endif
            @if($comment)
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6; font-weight: bold;">Comment</td>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">{{ $comment }}</td>
            </tr>
            @endif
        </table>

        <p style="color: #6c757d; font-size: 12px; margin-top: 20px;">
            This is an automated notification from QuickSMS HR Module. Please do not reply.
        </p>
    </div>
</body>
</html>
