@permission('googledrive manage')
@if(company_setting($module.'_drive'))
    @if($module == 'Projects')
        <div class="col-sm-auto">
            <a href="{{ route('googledrive.index',$module) }}" data-bs-original-title="{{ __('Google Drive') }}"  data-bs-toggle="tooltip" class="btn btn-sm btn-primary btn-icon-only width-auto me-2 ">
                <img src="{{ url('Modules/GoogleDrive/favicon.png') }}" width="15px" alt="">
            </a>
        </div>
    @else
        <a href="{{ route('googledrive.index',$module) }}" data-bs-original-title="{{ __('Google Drive') }}" data-title="{{__('Google Drive')}}"  data-bs-toggle="tooltip" id="drive_hook" class="btn btn-sm btn-primary me-2" >
            <img src="{{ url('Modules/GoogleDrive/favicon.png') }}" width="15px" alt="">
        </a>
    @endif
@endif
@endpermission

