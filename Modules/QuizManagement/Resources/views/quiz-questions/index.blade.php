@extends('layouts.main')
@section('page-title')
    {{ __('Manage Quiz Questions') }}
@endsection
@section('page-breadcrumb')
    {{ __('Quiz Questions') }}
@endsection
@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
@endpush
@section('page-action')
    <div class="d-flex">
        @permission('quizzes create')
            <a data-size="lg" data-url="{{ route('quiz.questions.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip"
                title="{{ __('Create') }}" data-title="{{ __('Create Quiz Question') }}" class="btn btn-sm btn-primary">
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
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    {{ $dataTable->scripts() }}
    <script>
        $('body').on('change', 'input[name="type"]', function() {
            var value = $(this).val();
            if (value == 'mcq') {
                $('.mcqs_repeater').removeClass('d-none');
            } else {
                $('.mcqs_repeater').addClass('d-none');
            }

            if (value == 'true_false') {
                $('.true_false_repeater').removeClass('d-none');
            } else {
                $('.true_false_repeater').addClass('d-none');
            }

        });
    </script>
    <script>
        $('#commonModal').on('shown.bs.modal', function() {
            var selector = "body";

            /** ================= MCQ REPEATER ================= */
            if ($(selector + " .mcqs_repeater").length) {
                var $dragAndDropMCQ = $(selector + " .mcqs_repeater .repeater-field").sortable({
                    handle: '.sort-handler'
                });

                var $mcqRepeater = $(selector + ' .mcqs_repeater').repeater({
                    initEmpty: false,
                    show: function() {
                        var itemCount = $(selector + " .mcqs_repeater [data-repeater-item]").length;
                        if (itemCount >= 5) return false; // Prevent adding new elements

                        $(this).slideDown();
                        updateButtonStateMCQ();
                    },
                    hide: function(deleteElement) {
                        if (confirm('Are you sure you want to delete this element?')) {
                            $(this).slideUp(function() {
                                $(this).remove();
                                updateButtonStateMCQ();
                            });
                        }
                    },
                    ready: function(setIndexes) {
                        $dragAndDropMCQ.on('drop', setIndexes);
                    },
                    isFirstItemUndeletable: true
                });

                var value = $(selector + " .mcqs_repeater").attr('data-value');

                if (value) {
                    try {
                        value = value.replace(/&quot;/g, '"'); // Fix encoded quotes
                        value = JSON.parse(value); // First parse
                        if (typeof value === "string") value = JSON.parse(value); // Parse again if needed


                        if (value.type === "mcq" && value.values) {
                            let MCQValues = Object.entries(value.values).map(([key, val]) => ({
                                mcq_values: val
                            }));


                            setTimeout(() => {
                                $mcqRepeater.setList(MCQValues);
                            }, 300); // Ensure it's executed after initialization
                        }
                    } catch (error) {
                        console.error("Error parsing MCQ JSON:", error);
                    }
                }

                function updateButtonStateMCQ() {
                    var itemCount = $(selector + " .mcqs_repeater [data-repeater-item]").length;
                    var addButton = $(".mcqs_repeater [data-repeater-create]");

                    if (itemCount >= 4) {
                        addButton.prop("disabled", true);
                    } else {
                        addButton.prop("disabled", false);
                    }
                }

                $(document).on("click", "[data-repeater-create]", function() {
                    setTimeout(updateButtonStateMCQ, 100);
                });

                $(document).on("click", "[data-repeater-delete]", function() {
                    setTimeout(updateButtonStateMCQ, 100);
                });

                updateButtonStateMCQ();
            }

            /** ================= TRUE/FALSE REPEATER ================= */
            if ($(selector + " .true_false_repeater").length) {
                var $dragAndDropTF = $("body .true_false_repeater .repeater-field").sortable({
                    handle: '.sort-handler'
                });

                var $tfRepeater = $(selector + ' .true_false_repeater').repeater({
                    initEmpty: false,
                    show: function() {
                        var itemCount = $(selector + " .true_false_repeater [data-repeater-item]")
                            .length;
                        if (itemCount >= 3) return false; // Prevent adding new elements

                        $(this).slideDown();
                        updateButtonStateTF();
                    },
                    hide: function(deleteElement) {
                        if (confirm('Are you sure you want to delete this element?')) {
                            $(this).slideUp(function() {
                                $(this).remove();
                                updateButtonStateTF();
                            });
                        }
                    },
                    ready: function(setIndexes) {
                        $dragAndDropTF.on('drop', setIndexes);
                    },
                    isFirstItemUndeletable: true
                });

                var value = $(selector + " .true_false_repeater").attr('data-value');

                if (value) {
                    try {
                        value = value.replace(/&quot;/g, '"'); // Fix encoded quotes
                        value = JSON.parse(value); // First parse
                        if (typeof value === "string") value = JSON.parse(value); // Parse again if needed

                        if (value.type === "true_false" && value.values) {
                            let formattedValues = Object.entries(value.values).map(([key, val]) => ({
                                true_false_values: val
                            }));
                            setTimeout(() => {
                                $tfRepeater.setList(formattedValues);
                            }, 300);
                        }
                    } catch (error) {
                        console.error("Error parsing True/False JSON:", error);
                    }
                }

                function updateButtonStateTF() {
                    var itemCount = $(selector + " .true_false_repeater [data-repeater-item]").length;
                    var addButton = $(".true_false_repeater [data-repeater-create]");

                    if (itemCount >= 2) {
                        addButton.prop("disabled", true);
                    } else {
                        addButton.prop("disabled", false);
                    }
                }

                $(document).on("click", "[data-repeater-create]", function() {
                    setTimeout(updateButtonStateTF, 100);
                });

                $(document).on("click", "[data-repeater-delete]", function() {
                    setTimeout(updateButtonStateTF, 100);
                });

                updateButtonStateTF();
            }
        });
    </script>
@endpush
