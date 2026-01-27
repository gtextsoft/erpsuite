@permission('quizparticipants delete')
    <div class="action-btn">
        {!! Form::open(['method' => 'DELETE', 'route' => ['quiz.participate.destroy', $quizParticipants->id]]) !!}
        <a href="#!" class="btn btn-sm   align-items-center text-white show_confirm bg-danger" data-bs-toggle="tooltip"
            title='Delete'>
            <i class="ti ti-trash"></i>
        </a>
        {!! Form::close() !!}
    </div>
@endpermission
