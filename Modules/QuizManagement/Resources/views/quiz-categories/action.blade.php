@permission('quizcategories edit')
    <div class="action-btn me-2">
        <a data-size="md" data-url="{{ route('quiz.categories.edit', $quizCategories->id) }}"
            class="btn btn-sm align-items-center text-white bg-info" data-ajax-popup="true" data-bs-toggle="tooltip"
            data-title="{{ __('Edit Quiz Categories') }}" title="{{ __('Edit') }}"><i class="ti ti-pencil"></i></a>
    </div>
@endpermission
@permission('quizcategories delete')
    <div class="action-btn">
        {!! Form::open(['method' => 'DELETE', 'route' => ['quiz.categories.destroy', $quizCategories->id]]) !!}
        <a href="#!" class="btn btn-sm   align-items-center text-white show_confirm bg-danger" data-bs-toggle="tooltip"
            title='Delete'>
            <i class="ti ti-trash"></i>
        </a>
        {!! Form::close() !!}
    </div>
@endpermission
