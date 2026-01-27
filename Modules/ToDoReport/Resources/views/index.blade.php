<!DOCTYPE html>
<html>
<head>
    <title>To-Do AI Report</title>
    <!-- Bootstrap CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .card h5 {
            margin-bottom: 1rem;
        }
        .modal-body pre {
            white-space: pre-wrap;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h2 class="mb-4">To-Do AI Report</h2>
        <div class="row">
            @foreach ($users as $user)
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm p-3">
                        <h5>{{ $user->name }}</h5>
                        <button class="btn btn-primary generate-report" data-user="{{ $user->id }}">
                            Generate Report
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">AI Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="reportContent">
                    <p class="text-muted">Generating...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $('.generate-report').on('click', function () {
            const userId = $(this).data('user');
            $('#reportContent').html('<p class="text-muted">Generating...</p>');
            const modal = new bootstrap.Modal(document.getElementById('reportModal'));
            modal.show();

            $.post("{{ route('todo.report.generate') }}", {
                _token: '{{ csrf_token() }}',
                user_id: userId
            }, function (data) {
                $('#reportContent').html(`<pre>${data.report}</pre>`);
            }).fail(function () {
                $('#reportContent').html('<p class="text-danger">Failed to generate report.</p>');
            });
        });
    </script>
</body>
</html>
