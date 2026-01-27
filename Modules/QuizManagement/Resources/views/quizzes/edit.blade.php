{{ Form::model($quiz, ['route' => ['quizzes.update', $quiz->id], 'method' => 'PUT', 'class' => 'w-100 needs-validation ', 'novalidate', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Enter Name']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('category', __('Category'), ['class' => 'form-label']) }}
                {{ Form::select('category', $quizCategories, $quiz->category_id, ['class' => 'form-control', 'id' => 'category', 'placeholder' => 'Select Category']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group switch-width">
                {{ Form::label('quiz_question', __('Quiz Questions'), ['class' => 'form-label']) }}
                <div id="quiz_question_list">
                    @foreach ($quizQuestions as $quizQuestion)
                        @php
                            $isChecked = $quiz->questions->contains($quizQuestion->id);
                        @endphp
                        <div class="form-check">
                            <input class="form-check-input quiz-question-checkbox" type="checkbox"
                                id="quiz_{{ $quizQuestion->id }}" name="quiz_question[]" value="{{ $quizQuestion->id }}"
                                data-marks="{{ $quizQuestion->marks }}" {{ $isChecked ? 'checked' : '' }}>
                            <label class="form-check-label" for="quiz_{{ $quizQuestion->id }}">
                                {{ $quizQuestion->question }} - ({{ $quizQuestion->marks }} {{ __('Marks') }})
                            </label>
                        </div>
                    @endforeach
                </div>
                @if ($quizQuestions->isEmpty())
                    <div class="text-xs">
                        {{ __('Please add Quiz Questions. ') }}
                        <a href="{{ route('quiz.questions.index') }}"><b>{{ __('Add Quiz Questions') }}</b></a>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('total_marks', __('Total Marks'), ['class' => 'form-label']) }}
                {{ Form::text('total_marks', null, ['class' => 'form-control', 'id' => 'total_marks', 'placeholder' => 'Enter Total Marks', 'readonly' => 'readonly']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('passing_marks', __('Passing Marks'), ['class' => 'form-label']) }}
                {{ Form::text('passing_marks', null, ['class' => 'form-control', 'placeholder' => 'Enter Quiz Name']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('per_qus_time', __('Per Question Time Limit (Seconds)'), ['class' => 'form-label']) }}
                {{ Form::select('per_qus_time', $minutes, null, ['class' => 'form-control', 'id' => 'per_qus_time']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('total_time_duration', __('Total Time Duration'), ['class' => 'form-label']) }}
                {{ Form::text('total_time_duration', null, ['class' => 'form-control', 'placeholder' => 'Total Time Duration', 'readonly' => 'readonly']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('color', __('Color'), ['class' => 'form-label']) }}
                <div class="theme-color themes-color">
                    <label class="theme-option">
                        <input type="radio" name="color" value="quiz-card-color-1"
                            @if ($quiz->color == 'quiz-card-color-1') checked @endif>
                        <span
                            class="themes-color-change rounded-circle  @if ($quiz->color == 'quiz-card-color-1') active_color @endif"
                            style="background: linear-gradient(126.29deg, #EF6F9C 1.72%, #EC8771 94.06%);"
                            data-value="quiz-card-color-1"></span>
                    </label>

                    <label class="theme-option">
                        <input type="radio" name="color" value="quiz-card-color-2"
                            @if ($quiz->color == 'quiz-card-color-2') checked @endif>
                        <span
                            class="themes-color-change rounded-circle @if ($quiz->color == 'quiz-card-color-2') active_color @endif"
                            style="background: linear-gradient(124.97deg, #467EEF -1.63%, #14B0F6 98.33%);"
                            data-value="quiz-card-color-2"></span>
                    </label>

                    <label class="theme-option">
                        <input type="radio" name="color" value="quiz-card-color-3"
                            @if ($quiz->color == 'quiz-card-color-3') checked @endif>
                        <span
                            class="themes-color-change rounded-circle @if ($quiz->color == 'quiz-card-color-3') active_color @endif"
                            style="background: linear-gradient(123.29deg, #477C4A -15.4%, #82E186 98.8%);"
                            data-value="quiz-card-color-3"></span>
                    </label>

                    <label class="theme-option">
                        <input type="radio" name="color" value="quiz-card-color-4"
                            @if ($quiz->color == 'quiz-card-color-4') checked @endif>
                        <span
                            class="themes-color-change rounded-circle @if ($quiz->color == 'quiz-card-color-4') active_color @endif"
                            style="background: linear-gradient(126.29deg, #7E75B9 1.72%, #BBB1FB 94.06%);"
                            data-value="quiz-card-color-4"></span>
                    </label>

                    <label class="theme-option">
                        <input type="radio" name="color" value="quiz-card-color-5"
                            @if ($quiz->color == 'quiz-card-color-5') checked @endif>
                        <span
                            class="themes-color-change rounded-circle @if ($quiz->color == 'quiz-card-color-5') active_color @endif"
                            style="background: linear-gradient(124.97deg, #C88F3A -1.63%, #F9C981 98.33%);"
                            data-value="quiz-card-color-5"></span>
                    </label>

                    <label class="theme-option">
                        <input type="radio" name="color" value="quiz-card-color-6"
                            @if ($quiz->color == 'quiz-card-color-6') checked @endif>
                        <span
                            class="themes-color-change rounded-circle @if ($quiz->color == 'quiz-card-color-6') active_color @endif"
                            style="background: linear-gradient(123.29deg, #C753B5 -15.4%, #F8AFED 98.8%);"
                            data-value="quiz-card-color-6"></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                {{ Form::select('status', ['0' => 'Draft', '1' => 'Publish'], null, ['class' => 'form-control', 'placeholder' => 'Select Status', 'id' => 'status']) }}
            </div>
        </div>
        <div class="col-12 field" data-name="attachments">
            <div class="attachment-upload">
                <div class="attachment-button">
                    <div class="pull-left">
                        <div class="form-group">
                            {{ Form::label('image', __('Image'), ['class' => 'form-label']) }}
                            <input type="file" name="image" class="form-control mb-3"
                                onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                            <img id="blah" class="mt-2" src="{{ isset($quiz->image) ? get_file($quiz->image) : asset('packages/Modules/ProductService/src/Resources/assets/image/img01.jpg') }}" style="width:30%;" />
                        </div>
                    </div>
                </div>
                <div class="attachments"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}


<script>
    document.querySelectorAll('.theme-option input').forEach(input => {
        input.addEventListener('change', function() {
            document.querySelectorAll('.themes-color-change').forEach(el => el.classList.remove(
                'active_color'));
            this.nextElementSibling.classList.add('active_color');
        });
    });
</script>
