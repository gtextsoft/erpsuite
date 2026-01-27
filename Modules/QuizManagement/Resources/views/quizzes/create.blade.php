
{{ Form::open(['route' => 'quizzes.store', 'method' => 'POST', 'class' => 'w-100 needs-validation ', 'novalidate', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Enter Quiz Name']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('category', __('Category'), ['class' => 'form-label']) }}
                {{ Form::select('category', $quizCategories, null, ['class' => 'form-control', 'id' => 'category', 'placeholder' => 'Select Category', 'required' => 'required']) }}
                @if ($quizCategories->isEmpty())
                    <div class="text-xs">
                        {{ __('Please add Quiz Categories. ') }}
                        <a href="{{ route('quiz.categories.index') }}"><b>{{ __('Add Quiz Categories') }}</b></a>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('question_category', __('Question Category'), ['class' => 'form-label']) }}
                {{ Form::select('question_category', $categories->pluck('name', 'id'), null, ['class' => 'form-control', 'id' => 'question_category', 'placeholder' => 'Select Question Category']) }}
                @if ($categories->isEmpty())
                    <div class="text-xs">
                        {{ __('Please add Question Categories. ') }}
                        <a href="{{ route('quiz.questions.index') }}"><b>{{ __('Add Question Categories') }}</b></a>
                    </div>
                @endif
            </div>
        </div>
        <div class="form-group">
            <label>Select Questions</label>
            <div id="quiz_question_list" style="max-height: 250px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                <p class="text-muted">Select a Question Category to load questions.</p>
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
                {{ Form::text('passing_marks', null, ['class' => 'form-control', 'placeholder' => 'Enter Passing Marks']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('per_qus_time', __('Per Question Time Limit (Seconds)'), ['class' => 'form-label']) }}
                {{ Form::select('per_qus_time', $minutes, null, ['class' => 'form-control', 'id' => 'per_qus_time', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('total_time_duration', __('Total Time Duration'), ['class' => 'form-label']) }}
                {{ Form::text('total_time_duration', null, ['class' => 'form-control', 'placeholder' => 'Total Time Duration', 'readonly' => 'readonly', 'id' => 'total_time_duration']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('color', __('Color'), ['class' => 'form-label']) }}
                <div class="theme-color themes-color">
                    <label class="theme-option">
                        <input type="radio" name="color" value="quiz-card-color-1" checked>
                        <span class="themes-color-change rounded-circle active_color" style="background: linear-gradient(126.29deg, #EF6F9C 1.72%, #EC8771 94.06%);" data-value="quiz-card-color-1"></span>
                    </label>
                    <label class="theme-option">
                        <input type="radio" name="color" value="quiz-card-color-2">
                        <span class="themes-color-change rounded-circle" style="background: linear-gradient(124.97deg, #467EEF -1.63%, #14B0F6 98.33%);" data-value="quiz-card-color-2"></span>
                    </label>
                    <label class="theme-option">
                        <input type="radio" name="color" value="quiz-card-color-3">
                        <span class="themes-color-change rounded-circle" style="background: linear-gradient(123.29deg, #477C4A -15.4%, #82E186 98.8%);" data-value="quiz-card-color-3"></span>
                    </label>
                    <label class="theme-option">
                        <input type="radio" name="color" value="quiz-card-color-4">
                        <span class="themes-color-change rounded-circle" style="background: linear-gradient(126.29deg, #7E75B9 1.72%, #BBB1FB 94.06%);" data-value="quiz-card-color-4"></span>
                    </label>
                    <label class="theme-option">
                        <input type="radio" name="color" value="quiz-card-color-5">
                        <span class="themes-color-change rounded-circle" style="background: linear-gradient(124.97deg, #C88F3A -1.63%, #F9C981 98.33%);" data-value="quiz-card-color-5"></span>
                    </label>
                    <label class="theme-option">
                        <input type="radio" name="color" value="quiz-card-color-6">
                        <span class="themes-color-change rounded-circle" style="background: linear-gradient(123.29deg, #C753B5 -15.4%, #F8AFED 98.8%);" data-value="quiz-card-color-6"></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                {{ Form::select('status', ['0' => 'Draft', '1' => 'Publish'], null, ['class' => 'form-control', 'placeholder' => 'Select Status', 'id' => 'status', 'required' => 'required']) }}
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
                            <img id="blah" class="mt-2" src="" style="width:30%;" />
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
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
document.querySelectorAll('.theme-option input').forEach(input => {
    input.addEventListener('change', function() {
        document.querySelectorAll('.themes-color-change').forEach(el => el.classList.remove('active_color'));
        this.nextElementSibling.classList.add('active_color');
    });
});

// Event delegation for dynamically loaded checkboxes
document.getElementById('quiz_question_list').addEventListener('change', function(e) {
    if (e.target.classList.contains('quiz-question-checkbox')) {
        updateTotals();
    }
});

function updateTotals() {
    let totalMarks = 0;
    let perQuestionTime = parseInt(document.getElementById('per_qus_time')?.value || 0);
    let questionCount = 0;

    document.querySelectorAll('input[name="quiz_question[]"]:checked').forEach(input => {
        const marks = parseFloat(input.getAttribute('data-marks')) || 0;
        totalMarks += marks;
        questionCount++;
    });

    document.getElementById('total_marks').value = totalMarks;

    if (perQuestionTime > 0 && questionCount > 0) {
        document.getElementById('total_time_duration').value = perQuestionTime * questionCount;
    } else {
        document.getElementById('total_time_duration').value = '';
    }
}

// Update total time on time selection too
document.getElementById('per_qus_time')?.addEventListener('change', updateTotals);

// AJAX load questions by category
document.getElementById('question_category').addEventListener('change', function () {
    const categoryId = this.value;
    const questionWrapper = document.getElementById('quiz_question_list');

    if (!categoryId) {
        questionWrapper.innerHTML = '<p class="text-muted">Select a Question Category to load questions.</p>';
        updateTotals();
        return;
    }

    fetch(`/quizmanagement/ajax/questions-by-category/${categoryId}`)
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                questionWrapper.innerHTML = '<p class="text-danger">No questions available for this category.</p>';
            } else {
                questionWrapper.innerHTML = data.map(q => `
                    <div class="form-check">
                        <input class="form-check-input quiz-question-checkbox" type="checkbox" name="quiz_question[]" value="${q.id}" id="q-${q.id}" data-marks="${q.marks}">
                        <label class="form-check-label" for="q-${q.id}">
                            ${q.question} - (${q.marks} Marks)
                        </label>
                    </div>
                `).join('');
            }
            updateTotals();
        })
        .catch(error => {
            console.error('Error fetching questions:', error);
            questionWrapper.innerHTML = '<p class="text-danger">Error loading questions.</p>';
            updateTotals();
        });
});

// Run once on page load in case of pre-selected values
updateTotals();
</script>