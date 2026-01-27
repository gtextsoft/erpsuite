@permission('zoommeeting show')
    <div class="action-btn me-2">
        <a href="#"
            data-url="{{ route('zoom-meeting.show', $zoomMeeting->id) }}"
            data-size="md" data-ajax-popup="true"
            data-title="{{ __('View Zoom Meeting') }}"
            class="mx-3 btn btn-sm d-inline-flex align-items-center bg-warning"
            data-bs-whatever="{{ __('View Meeting') }}"
            data-bs-toggle="tooltip" title=""
            data-bs-original-title="{{ __('View') }}">
            <span class="text-white">
                <i class="ti ti-eye"></i>
            </span>
        </a>
    </div>
@endpermission

@permission('zoommeeting delete')
    <div class="action-btn ">
        {!! Form::open([
            'method' => 'DELETE',
            'route' => ['zoom-meeting.destory', $zoomMeeting->id],
            'id' => 'delete-form-' . $zoomMeeting->id,
        ]) !!}
        <a href="#"
            class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
            data-bs-toggle="tooltip" title="{{ __('Delete') }}"><i
                class="ti ti-trash text-white text-white"></i></a>
        {!! Form::close() !!}
    </div>
@endpermission
