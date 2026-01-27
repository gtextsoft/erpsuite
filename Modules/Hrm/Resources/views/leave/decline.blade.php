{{ Form::open(['url' => route('leave.decline.store', $leave->id), 'method' => 'POST']) }}
    @csrf
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-12">
                {{ Form::label('final_remark', __('Final Remark'), ['class' => 'form-label']) }}
                {{ Form::textarea('final_remark', null, ['class' => 'form-control', 'rows' => '3', 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Decline') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}