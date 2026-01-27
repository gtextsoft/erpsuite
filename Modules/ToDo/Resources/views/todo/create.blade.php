@php
    $setting = getCompanyAllSetting();
@endphp

{{ Form::open(['route' => 'to-do.store', 'method' => 'post', 'class'=>'needs-validation','novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Title')]) }}
                </div>
            </div>
        </div>

        {{-- Updated Duration Field --}}
        <div class="col-lg-12 form-group">
            <label class="form-label">{{ __('Duration') }}</label>
            <div class='input-group mb-2'>
                <input type='text' class="form-control" name="current_date_display" value="{{ now()->format('M d, Y') }}" readonly>
                <input type="hidden" name="start_date" value="{{ now()->format('Y-m-d H:i:s') }}">
                <input type="number" class="form-control" name="hours_duration" id="hours_duration" min="1" placeholder="{{ __('Enter hours') }}" required>
                <input type="hidden" name="due_date" id="due_date">
                <span class="input-group-text"><i class="feather icon-clock"></i></span>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="form-group">
                {{ Form::label('assigned_to', __('Assigned To'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    <select class="multi-select choices" id="users_list" name="assigned_to[]" multiple="multiple" data-placeholder="{{ __('Select Users ...') }}">
                        @foreach ($users as $key => $user)
                            <option value="{{ $key }}">{{ $user }}</option>
                        @endforeach
                    </select>
                    <p class="text-danger d-none" id="user_validation">{{ __('Users field is required.') }}</p>
                </div>
            </div>
        </div>

       

        {{-- Descriptions --}}
        <div class="col-lg-12">
            <div class="form-group">
                {{ Form::label('descriptions', __('Descriptions'), ['class' => 'form-label']) }}
                <div id="description-container">
                    <div class="input-group mb-2 description-field">
                        <span class="input-group-text description-number">1.</span>
                        {{ Form::text('descriptions[]', null, ['class' => 'form-control', 'placeholder' => __('Enter Description')]) }}
                        <button type="button" class="btn btn-danger remove-description" style="display: none;">&times;</button>
                    </div>
                </div>
                <button type="button" class="btn btn-primary" id="add-description">{{ __('Add Description') }}</button>
            </div>
        </div>

        {{-- Comments --}}
        <div class="col-lg-12">
            <div class="form-group">
                {{ Form::label('comments', __('Comments'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::textarea('comments', null, ['class' => 'form-control', 'placeholder' => __('Enter your comments'), 'rows' => 3]) }}
                </div>
            </div>
        </div>

        <div class="row">
            @stack('calendar')
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary" id="submit">
</div>
{{ Form::close() }}

{{-- Required Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('Modules/ToDo/Resources/assets/libs/moment/min/moment.min.js') }}"></script>

<script>
    $(document).ready(function () {
        let descriptionIndex = 1;

        // Validate assigned users on submit
        $('#submit').click(function () {
            var userCount = $("#users_list option:selected").length;
            if (userCount === 0) {
                $('#user_validation').removeClass('d-none');
                return false;
            } else {
                $('#user_validation').addClass('d-none');
            }
        });

        // Add new description input
        $('#add-description').click(function () {
            descriptionIndex++;
            const newField = `
                <div class="input-group mb-2 description-field">
                    <span class="input-group-text description-number">${descriptionIndex}.</span>
                    <input type="text" name="descriptions[]" class="form-control" placeholder="{{ __('Enter Description') }}">
                    <button type="button" class="btn btn-danger remove-description">&times;</button>
                </div>`;
            $('#description-container').append(newField);
            updateDescriptionNumbers();
        });

        // Remove description input
        $(document).on('click', '.remove-description', function () {
            $(this).closest('.description-field').remove();
            updateDescriptionNumbers();
        });

        // Re-index description numbers
        function updateDescriptionNumbers() {
            $('.description-field').each(function (index) {
                $(this).find('.description-number').text((index + 1) + '.');
            });
        }

        // Calculate due_date from current date + hours
        $('#hours_duration').on('input', function () {
            const hours = parseInt($(this).val());
            if (!isNaN(hours) && hours > 0) {
                const startDate = moment("{{ now()->format('Y-m-d H:i:s') }}");
                const dueDate = startDate.clone().add(hours, 'hours');
                $('input[name="due_date"]').val(dueDate.format('YYYY-MM-DD HH:mm:ss'));
            } else {
                $('input[name="due_date"]').val('');
            }
        });
    });
</script>
