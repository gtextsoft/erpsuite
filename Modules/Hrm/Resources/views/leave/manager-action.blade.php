<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table">
                <tr role="row">
                    <th>{{ __('Employee') }}</th>
                    <td>{{ !empty($employee->name) ? $employee->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Leave Type') }}</th>
                    <td>{{ !empty($leavetype->title) ? $leavetype->title : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Applied On') }}</th>
                    <td>{{ \Auth::user()->dateFormat($leave->applied_on) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Start Date') }}</th>
                    <td>{{ \Auth::user()->dateFormat($leave->start_date) }}</td>
                </tr>
                <tr>
                    <th>{{ __('End Date') }}</th>
                    <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Leave Reason') }}</th>
                    <td>{{ $leave->leave_reason }}</td>
                </tr>
                <tr>
                    <th>{{ __('Remark') }}</th>
                    <td>{{ $leave->remark }}</td>
                </tr>
            </table>
        </div>
    </div>
    
    {{ Form::open(['url' => 'leave/manager/changeaction', 'method' => 'post']) }}
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('status', __('Status'), ['class' => 'col-form-label']) }}
                <select name="status" id="status" class="form-control select2" required>
                    <option value="">{{ __('Select Status') }}</option>
                    <option value="Approved">{{ __('Approved') }}</option>
                    <option value="Rejected">{{ __('Rejected') }}</option>
                </select>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('remark', __('Remark'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('remark', null, ['class' => 'form-control', 'placeholder' => __('Enter Remark'), 'rows' => 3, 'required' => 'required']) }}
            </div>
        </div>
        <input type="hidden" name="leave_id" value="{{ $leave->id }}">
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        {{ Form::submit(__('Submit'), ['class' => 'btn btn-primary']) }}
    </div>
    {{ Form::close() }}
</div>