@extends('layouts.main')
@section('page-title')
    {{ __('Manage Quizzes') }}
@endsection
@section('page-breadcrumb')
    {{ __('Quizzes') }}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('Modules/QuizManagement/Resources/assets/css/custom.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
@endpush
@section('page-action')
    <div class="d-flex">
        @permission('quizzes create')
            <a data-size="lg" data-url="{{ route('quizzes.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip"
                title="{{ __('Create') }}" data-title="{{ __('Create Quiz') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive overflow_hidden">
                        {{ $dataTable->table(['width' => '100%']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    {{ $dataTable->scripts() }}
    <script>
        $(document).on('change', '.quiz-question-checkbox', function() {
            let totalMarks = 0;
            let selectedQuestions = [];
            $(".quiz-question-checkbox:checked").each(function() {
                let marks = parseInt($(this).data("marks")) || 0;
                totalMarks += marks;
                selectedQuestions.push($(this).next("label").text().trim());
            });
            $("#total_marks").val(totalMarks);
        });

        function previewFiles(input) {
            var preview = document.getElementById('attachmentsPreview');
            preview.innerHTML = '';
            if (input.files) {
                Array.from(input.files).forEach(file => {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.width = '20%';
                        img.style.marginRight = '10px';
                        preview.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                });
            }
        }

        $(document).ready(function() {
            function updateTotalTime() {
                let perQuestionTime = parseInt($('#per_qus_time').val()) || 0;
                let checkedQuestions = $('.quiz-question-checkbox:checked').length;
                let totalTime = perQuestionTime * checkedQuestions;
                console.log("Selected Per Question Time:", perQuestionTime);
                console.log("Checked Questions:", checkedQuestions);
                console.log("Total Time:", totalTime);
                $('#total_time_duration').val(totalTime);
            }

            $(document).on('change', '#per_qus_time', function() {
                updateTotalTime();
            });

            $(document).on('change', '.quiz-question-checkbox', function() {
                updateTotalTime();
            });

            $(document).on('click', '.copy-link-btn', function(e) {
                e.preventDefault();
                const link = $(this).data('link');
                console.log('Copying link:', link);
                if (!link) {
                    console.error('No link found in data-link attribute');
                    alert('No link to copy.');
                    return;
                }

                if (navigator.clipboard) {
                    navigator.clipboard.writeText(link).then(() => {
                        console.log('Clipboard API success');
                        alert("{{ __('Link copied!') }}");
                    }).catch(err => {
                        console.error('Clipboard API failed: ', err);
                        alert('Failed to copy link.');
                    });
                } else {
                    console.log('Using fallback copy method');
                    const textArea = document.createElement('textarea');
                    textArea.value = link;
                    document.body.appendChild(textArea);
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        console.log('Fallback copy success');
                        alert("{{ __('Link copied!') }}");
                    } catch (err) {
                        console.error('Fallback copy failed: ', err);
                        alert('Failed to copy link.');
                    }
                    document.body.removeChild(textArea);
                }
            });
        });
    </script>
@endpush