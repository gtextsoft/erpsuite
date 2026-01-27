{{ Form::open(['route' => 'quiz.questions.store', 'method' => 'POST', 'class' => 'w-100 needs-validation ', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('question', __('Question'), ['class' => 'form-label']) }}
                {{ Form::text('question', null, ['class' => 'form-control', 'placeholder' => 'Enter Question']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
                <div class="d-flex gap-4">
                    <div class="form-check">
                        <input class="form-check-input" name="type" type="radio" id="mcq" value="mcq">
                        <label class="form-check-label" for="mcq">{{ __('MCQ') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" name="type" type="radio" id="true_false" value="true_false">
                        <label class="form-check-label" for="true_false">{{ __('True/False') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" name="type" type="radio" id="descriptive"
                            value="descriptive">
                        <label class="form-check-label" for="descriptive">{{ __('Descriptive') }}</label>
                    </div>
                    <div class="col-md-6">
    <div class="form-group">
        {{ Form::label('category_id', __('Question Category'), ['class' => 'form-label']) }}
        {{ Form::select('category_id', $categories, null, ['class' => 'form-control', 'placeholder' => 'Select Category']) }}
    </div>
</div>

                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group col-md-12 mcqs_repeater d-none">
                <div class="row">
                    <div class="col-12">
                        <div class="row flex-grow-1">
                            <div class="col-md d-flex align-items-center col-6">
                                {!! Form::label('mcq_values', __('MCQs Value'), ['class' => 'form-label']) !!}
                            </div>
                            <div class="col-md-6 justify-content-between align-items-center col-6">
                                <div class="col-md-12 d-flex align-items-center  justify-content-end">
                                    <a data-repeater-create="" class="btn btn-primary btn-sm add-row text-white"
                                        data-toggle="modal" data-target="#add-mcq">
                                        <i class="ti ti-plus"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="" data-repeater-list="mcq_values">
                            <div class="row mt-3 ui-sortable flex-grow-1 repeater-field" data-repeater-item>
                                <div class="col-md-10 justify-content-between align-items-center col-10">
                                    <input type="text" class="form-control name" name="mcq_values"
                                        placeholder="Please Enter MCQ Value">
                                </div>
                                <div class="col-md-2 justify-content-between align-items-center col-2"
                                    data-repeater-delete>
                                    <a href="#!"
                                        class="mx-3 btn btn-sm d-inline-flex align-items-center m-2 p-2 bg-danger desc_delete">
                                        <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('Delete') }}"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group col-md-12 true_false_repeater d-none">
                <div class="row">
                    <div class="col-12">
                        <div class="row flex-grow-1">
                            <div class="col-md d-flex align-items-center col-6">
                                {!! Form::label('true_false_values', __('True/False Values'), ['class' => 'form-label']) !!}
                            </div>
                            <div class="col-md-6 justify-content-between align-items-center col-6">
                                <div class="col-md-12 d-flex align-items-center  justify-content-end">
                                    <a data-repeater-create="" class="btn btn-primary btn-sm add-row text-white"
                                        data-toggle="modal" data-target="#add-mcq">
                                        <i class="ti ti-plus"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="" data-repeater-list="true_false_values">
                            <div class="row mt-3 ui-sortable flex-grow-1 repeater-field" data-repeater-item>
                                <div class="col-md-10 justify-content-between align-items-center col-10">
                                    <input type="text" class="form-control name" name="true_false_values"
                                        placeholder="Please Enter True/False Value">
                                </div>
                                <div class="col-md-2 justify-content-between align-items-center col-2"
                                    data-repeater-delete>
                                    <a href="#!"
                                        class="mx-3 btn btn-sm d-inline-flex align-items-center m-2 p-2 bg-danger desc_delete">
                                        <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('Delete') }}"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('correct_answer', __('Correct Answer'), ['class' => 'form-label']) }}
                {{ Form::text('correct_answer', null, ['class' => 'form-control', 'placeholder' => 'Enter Correct Answer']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('marks', __('Marks'), ['class' => 'form-label']) }}
                {{ Form::text('marks', null, ['class' => 'form-control', 'placeholder' => 'Enter Marks']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
