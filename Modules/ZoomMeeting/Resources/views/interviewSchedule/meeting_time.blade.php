<div class="form-group col-md-6 d-none zoom_start_time">
    {{ Form::label('zoom_start_time', __('Start Time'), ['class' => 'col-form-label']) }}<x-required></x-required>
    {{ Form::time('zoom_start_time', null, ['class' => 'form-control zoom_start_time']) }}
</div>
<div class="form-group col-md-6 d-none zoom_end_time">
    {{ Form::label('zoom_end_time', __('End Time'), ['class' => 'col-form-label']) }}<x-required></x-required>
    {{ Form::time('zoom_end_time', null, ['class' => 'form-control zoom_end_time']) }}
</div>
