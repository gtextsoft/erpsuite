@php
    $company_settings = getCompanyAllSetting();
@endphp

{{ Form::model($transfer, ['route' => ['transfer.update', $transfer->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="text-end">
        @if (module_is_active('AIAssistant'))
            @include('aiassistant::ai.generate_ai_btn', ['template_module' => 'transfer', 'module' => 'Hrm'])
        @endif
    </div>
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
                {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
                {{ Form::select('employee_id', $employees, $transfer->user_id, ['class' => 'form-control select2', 'placeholder' => __('Select Employee'), 'required' => 'required']) }}
                @error('employee_id')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('target_workspace_id', __('Workspace'), ['class' => 'col-form-label']) }}
                {{ Form::select('target_workspace_id', $workspaces, $transfer->target_workspace_id ?? getActiveWorkSpace(), ['class' => 'form-control select2', 'placeholder' => __('Select Workspace'), 'required' => 'required']) }}
                @error('target_workspace_id')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-6 branch-department-fields">
            <div class="form-group">
                {{ Form::label('branch_id', !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch'), ['class' => 'col-form-label']) }}
                {{ Form::select('branch_id', $branches, $transfer->branch_id, ['class' => 'form-control select2', 'placeholder' => __('Select '.(!empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch')))]) }}
                @error('branch_id')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-6 branch-department-fields">
            <div class="form-group">
                {{ Form::label('department_id', !empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department'), ['class' => 'col-form-label']) }}
                {{ Form::select('department_id', $departments, $transfer->department_id, ['class' => 'form-control select2', 'id' => 'department_id', 'placeholder' => __('Select '.(!empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department')))]) }}
                @error('department_id')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('transfer_date', __('Transfer Date'), ['class' => 'col-form-label']) }}
                {{ Form::date('transfer_date', $transfer->transfer_date, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Select Date')]) }}
                @error('transfer_date')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('description', $transfer->description, ['class' => 'form-control', 'placeholder' => __('Enter Description'), 'rows' => 3, 'required' => 'required']) }}
                @error('description')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Update'), ['class' => 'btn btn-primary']) }}
</div>
{{ Form::close() }}

@push('scripts')
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: function () {
                    return $(this).attr('placeholder');
                },
                allowClear: true
            });

            // Toggle branch/department fields based on workspace selection
            function toggleBranchDepartmentFields() {
                var currentWorkspaceId = '{{ getActiveWorkSpace() }}';
                var selectedWorkspaceId = $('#target_workspace_id').val();
                var $branchDepartmentFields = $('.branch-department-fields');

                if (selectedWorkspaceId === currentWorkspaceId) {
                    $branchDepartmentFields.show();
                    $branchDepartmentFields.find('select').prop('required', true);
                } else {
                    $branchDepartmentFields.hide();
                    $branchDepartmentFields.find('select').prop('required', false);
                }
            }

            // Initial check
            toggleBranchDepartmentFields();

            // On workspace change
            $('#target_workspace_id').on('change', function () {
                toggleBranchDepartmentFields();
            });
        });
    </script>
@endpush