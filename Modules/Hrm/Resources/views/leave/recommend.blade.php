{{ Form::open(['url' => route('leave.recommend.store', $leave->id), 'method' => 'POST']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-12">
                {{ Form::label('recommender_remark', __('Recommender Remark'), ['class' => 'form-label']) }}
                {{ Form::textarea('recommender_remark', null, ['class' => 'form-control', 'rows' => '3', 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Recommend') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}