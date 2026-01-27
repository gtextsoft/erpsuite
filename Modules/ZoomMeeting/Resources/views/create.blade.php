<form method="POST" class="needs-validation" novalidate action="{{ route('zoom-meeting.store') }}" accept-charset="UTF-8">
    @csrf
    <div class="modal-body">
        <div class="text-end">
            @if (module_is_active('AIAssistant'))
                @include('aiassistant::ai.generate_ai_btn', [
                    'template_module' => 'zoom_metting',
                    'module' => 'ZoomMeeting',
                ])
            @endif
        </div>
        <div class="row">
            <div class="form-group col-md-12 ">
                <label for="title" class="form-label">{{ __('Title') }}</label>
                <input class="form-control" placeholder="{{ __('Enter Meeting Title') }}" required="required"
                    name="title" type="text" id="title">
            </div>
            <div class="form-group col-md-6 ">
                <div>
                    {{ Form::label('user_id', __('Users'), ['class' => 'form-label']) }}
                    {{ Form::select('users[]', $users, null, ['class' => 'form-control choices', 'id' => 'choices-multiple', 'multiple' => '']) }}
                </div>
            </div>
            <div class="form-group col-md-6 ">
                <label for="datetime" class="form-label">{{ __('Start Date/Time') }}</label>
                <input class="form-control" value="{{ date('Y-m-d H:i') }}" placeholder="{{ __('Select Date/Time') }}"
                    required="required" name="start_date" type="datetime-local">
            </div>
            <div class="form-group col-md-6 ">
                <label for="duration" class="form-label">{{ __('Duration') }}</label>
                <input class="form-control" placeholder="{{ __('Enter Duration In Minutes') }}" required="required" name="duration"
                    type="number" id="duration">
            </div>

            <div class="form-group col-md-6">
                <label for="password" class="form-label">{{ __('Password') }} {{ __('( Optional )') }}</label>
                <input class="form-control" placeholder="{{ __('Enter Password') }}" name="password" type="password"
                    value="" id="password">
            </div>
            @stack('calendar')
        </div>
    </div>
    <div class="modal-footer">
        <div>
            <button type="button" class="btn  btn-light me-1" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button class="btn  btn-primary" type="submit" id="create-client">{{ __('Create') }}</button>
        </div>
    </div>
</form>
