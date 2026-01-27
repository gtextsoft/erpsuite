<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slack Messaging - ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7fa; font-family: 'Segoe UI', sans-serif; }
        .chat-container {
            max-width: 700px; margin: 60px auto; background: #fff;
            border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); padding: 30px;
        }
        .chat-box {
            max-height: 400px; overflow-y: auto; background: #fafafa;
            border-radius: 8px; padding: 15px; margin-bottom: 20px;
        }
        .msg { margin-bottom: 12px; padding: 10px 15px; border-radius: 8px; max-width: 80%; }
        .msg.erp { background: #e8f3ff; align-self: flex-end; }
        .msg.slack { background: #f0f0f0; }
        .msg small { display: block; font-size: 11px; color: #777; margin-top: 4px; }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header text-center">
        <h3 class="fw-bold text-primary">Slack Messaging</h3>
        <p class="text-muted mb-0">Post and view replies from Slack directly inside ERP</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- CHAT BOX --}}
    <div class="chat-box d-flex flex-column">
        @foreach($messages as $msg)
            <div class="msg {{ $msg->from_slack ? 'slack' : 'erp' }}">
                <strong>{{ $msg->from_slack ? 'Slack User' : 'ERP User' }}</strong>: 
                {{ $msg->message }}
                <small>{{ $msg->created_at->diffForHumans() }}</small>
            </div>
        @endforeach
    </div>

    {{-- FORM --}}
    <form method="POST" action="{{ route('slack.send') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Select Channel</label>
            <select name="channel" class="form-select" required>
                <option value="">-- Choose a Slack Channel --</option>
                <option value="#general">#general</option>
                <option value="#gtext-holdings">#gtext-holdings</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Message</label>
            <textarea name="message" class="form-control" rows="4" placeholder="Type your message..." required></textarea>
        </div>
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Send to Slack</button>
        </div>
    </form>
</div>

</body>
</html>
