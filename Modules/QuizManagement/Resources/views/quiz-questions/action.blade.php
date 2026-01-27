@permission('quizquestions edit')
    <div class="action-btn me-2">
        <a data-size="lg" data-url="{{ route('quiz.questions.edit', $quizQuestion->id) }}"
            class="btn btn-sm align-items-center text-white bg-info" data-ajax-popup="true" data-bs-toggle="tooltip"
            data-title="{{ __('Edit Quiz Question') }}" title="{{ __('Edit') }}"><i class="ti ti-pencil"></i></a>
    </div>
@endpermission
@permission('quizquestions delete')
    <div class="action-btn">
        {!! Form::open(['method' => 'DELETE', 'route' => ['quiz.questions.destroy', $quizQuestion->id]]) !!}
        <a href="#!" class="btn btn-sm   align-items-center text-white show_confirm bg-danger" data-bs-toggle="tooltip"
            title='Delete'>
            <i class="ti ti-trash"></i>
        </a>
        {!! Form::close() !!}
    </div>
@endpermission
