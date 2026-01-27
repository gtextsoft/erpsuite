@php
    $company_settings = getCompanyAllSetting();
@endphp

{{ Form::open(['route' => ['requisitions.approve', $requisition->id], 'method' => 'POST', 'class' => 'needs-validation', 'novalidate', 'id' => 'requisition-action-form']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('action', __('Action'), ['class' => 'col-form-label']) }}
                {{ Form::select('action', ['approve' => __('Approve'), 'reject' => __('Reject')], null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Action')]) }}
                @error('action')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('remark', __('Remark'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('remark', null, ['class' => 'form-control', 'rows' => 3, 'required' => 'required', 'placeholder' => __('Enter remark for approval or rejection')]) }}
                @error('remark')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Submit'), ['class' => 'btn btn-primary', 'id' => 'submit-action']) }}
</div>
{{ Form::close() }}

<script>
$(document).ready(function () {
    // Initialize Select2
    $('.select2').select2({
        placeholder: $(this).attr('placeholder'),
        allowClear: true
    });

    // Bootstrap form validation
    (function () {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();

    // AJAX form submission
    $('#requisition-action-form').on('submit', function (e) {
        e.preventDefault();

        // Clear previous messages
        $('.alert').remove();

        // Validate form
        if (!this.checkValidity()) {
            $(this).addClass('was-validated');
            return;
        }

        var formData = $(this).serialize();
        var submitButton = $('#submit-action');
        submitButton.prop('disabled', true).text('{{ __('Submitting...') }}');

        $.ajax({
            url: '{{ route('requisitions.approve', $requisition->id) }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    // Show success message and close modal
                    $('#requisition-action-form').before(
                        '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        response.success +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'
                    );
                    setTimeout(function () {
                        $('#actionModal').modal('hide');
                        location.reload();
                    }, 1500);
                }
            },
            error: function (xhr) {
                var errorMsg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : '{{ __('An error occurred.') }}';
                $('#requisition-action-form').before(
                    '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    errorMsg +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'
                );
                submitButton.prop('disabled', false).text('{{ __('Submit') }}');
            }
        });
    });
});
</script>