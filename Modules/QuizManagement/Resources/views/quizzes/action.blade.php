@permission('quizzes show')
    <div class="action-btn me-2">
        <a data-size="lg" data-url="{{ route('quizzes.show', $quizzes->id) }}"
            class="btn btn-sm align-items-center text-white bg-warning" data-ajax-popup="true" data-bs-toggle="tooltip"
            data-bs-title="{{ __('View Quiz Question') }}">
            <i class="ti ti-eye"></i>
        </a>
    </div>
@endpermission
@permission('quizzes edit')
    <div class="action-btn me-2">
        <a data-size="lg" data-url="{{ route('quizzes.edit', $quizzes->id) }}"
            class="btn btn-sm align-items-center text-white bg-info" data-ajax-popup="true" data-bs-toggle="tooltip"
            data-bs-title="{{ __('Edit Quiz') }}">
            <i class="ti ti-pencil"></i>
        </a>
    </div>
@endpermission
@permission('quizzes delete')
    <div class="action-btn">
        {!! Form::open(['method' => 'DELETE', 'route' => ['quizzes.destroy', $quizzes->id]]) !!}
        <a href="#!" class="btn btn-sm align-items-center text-white show_confirm bg-danger" data-bs-toggle="tooltip"
           data-bs-title="{{ __('Delete') }}">
            <i class="ti ti-trash"></i>
        </a>
        {!! Form::close() !!}
    </div>
@endpermission
@permission('quizzes show')
    <div class="action-btn">
        <a href="#!" class="btn btn-sm align-items-center text-white bg-primary copy-link-btn" 
           data-link="{{ route('quizzes.participate.show', [$workspace->slug, $quizzes->id]) }}"
           data-bs-toggle="tooltip" data-bs-title="{{ __('Copy Link') }}">
            <i class="ti ti-link"></i>
        </a>
    </div>
@endpermission