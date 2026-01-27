@php
    $company_settings = getCompanyAllSetting();
@endphp

{{ Form::open(['route' => 'requisitions.store', 'method' => 'POST', 'class' => 'needs-validation', 'novalidate', 'id' => 'requisition-create-form']) }}
<div class="modal-body">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('title', __('Title'), ['class' => 'col-form-label']) }}
                {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Requisition Title')]) }}
                @error('title')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <!--  <div class="col-md-6">-->
        <!--    <div class="form-group">-->
        <!--        {{ Form::label('amount', __('Amount'), ['class' => 'col-form-label']) }}-->
        <!--        {{ Form::number('amount', null, ['class' => 'form-control', 'required' => 'required', 'min' => '0.01', 'step' => '0.01', 'placeholder' => __('Enter Amount')]) }}-->
        <!--        @error('amount')-->
        <!--            <span class="invalid-feedback" role="alert">{{ $message }}</span>-->
        <!--        @enderror-->
        <!--    </div>-->
        <!--</div>-->
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('first_approver_id', __('First Approver'), ['class' => 'col-form-label']) }}
                @if (isset($approvers) && !empty($approvers) && count($approvers) > 0)
                    {{ Form::select('first_approver_id', $approvers, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select First Approver')]) }}
                @else
                    <p class="text-danger">{{ __('No approvers available. Please contact the administrator.') }}</p>
                    {{ Form::select('first_approver_id', [], null, ['class' => 'form-control select2', 'disabled' => 'disabled']) }}
                @endif
                @error('first_approver_id')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'required' => 'required', 'rows' => 3, 'placeholder' => __('Enter Requisition Description')]) }}
                @error('description')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Create'), ['class' => 'btn btn-primary', 'id' => 'submit-create', 'disabled' => empty($approvers) ? 'disabled' : null]) }}
</div>
{{ Form::close() }}

<script>
$(document).ready(function () {
    $('.select2').select2({
        placeholder: "{{ __('Select First Approver') }}",
        allowClear: true
    });

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

    $('#requisition-create-form').on('submit', function (e) {
        e.preventDefault();
        $('.alert').remove();
        if (!this.checkValidity()) {
            $(this).addClass('was-validated');
            return;
        }
        var formData = $(this).serialize();
        var submitButton = $('#submit-create');
        submitButton.prop('disabled', true).text('{{ __('Submitting...') }}');
        $.ajax({
            url: '{{ route('requisitions.store') }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    $('#requisition-create-form').before(
                        '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        response.success +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'
                    );
                    setTimeout(function () {
                        $('#ajaxModal').modal('hide');
                        location.reload();
                    }, 1500);
                }
            },
            error: function (xhr) {
                var errorMsg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : '{{ __('An error occurred.') }}';
                $('#requisition-create-form').before(
                    '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    errorMsg +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'
                );
                submitButton.prop('disabled', false).text('{{ __('Create') }}');
            }
        });
    });
});
</script>