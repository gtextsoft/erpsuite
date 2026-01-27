<!DOCTYPE html>
<html>
<head>
    <title>Zoom Meeting Invitation</title>
</head>
<body>
    <h2>Hello,</h2>
    <p>You have been invited to a Zoom meeting.</p>

    <p><strong>Title:</strong> {{ $meeting->title }}</p>
    <p><strong>Start Time:</strong> {{ $meeting->start_date }}</p>
    <p><strong>Duration:</strong> {{ $meeting->duration }} minutes</p>
    
    <p><strong>Join the Meeting:</strong> <a href="{{ $meeting->join_url }}">{{ $meeting->join_url }}</a></p>

    @if ($meeting->password)
        <p><strong>Meeting Password:</strong> {{ $meeting->password }}</p>
    @endif

    <p>Thank you!</p>
</body>
</html>
