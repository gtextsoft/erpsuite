@permission('googledrive manage')
@php
        $google_drive_modules = Modules\GoogleDrive\Entities\GoogleDriveSetting::get_modules();
        $company_settings        = getCompanyAllSetting();
@endphp
<div class="card" id="google-drive">
        {{ Form::open(array('route' => 'google.drive.setting.store','method' => 'post', 'enctype' => 'multipart/form-data')) }}
        <div class="card-header p-3">
                    <h5 class="">{{ __('Google Drive Settings') }}</h5>
                    <small><b class="text-danger">{{ __('Note: ') }}</b>{{ __('While creating json credentials add this URL in "Authorised redirect URIs" Section -') }} {{ env('APP_URL').'/google/callback' }} </small>

        </div>
        <div class="card-body p-3 pb-0">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                    @if (!isset($company_settings['google_drive_json_file']))
                        <p class="card-body p-0 text-danger">{{ __('To start the synchronization process, please insert your Google OAuth 2.0 JSON below.') }}</p>

                        {{ Form::label('google_drive_json_file', __('Google Drive JSON File'), ['class' => 'form-label']) }}
                        <input type="file" class="form-control" name="google_drive_json_file" id="google_drive_json_file">
                    @elseif (
                                !isset($company_settings['google_drive_token']) ||
                                !isset($company_settings['google_drive_refresh_token']) ||
                                $company_settings['google_drive_token'] == '' ||
                                $company_settings['google_drive_refresh_token'] == ''
                            )
                        <div class="row">
                            <div class="col-auto">
                                <p class="text-danger">{{ __('You have not authorized your Google account to browse and attach folders. Click') }} <a href="#" onclick="openGoogleDriveAuthenticationWindow()">{{ __('here') }}</a>{{ __(' to authorize.') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="#">
                <ul class="nav nav-pills gap-2 mb-3" id="pills-tab" role="tablist">
                    @php
                        $active = 'active';
                    @endphp
                    @foreach ($google_drive_modules as $key => $e_module)
                        @if((module_is_active($key) ) || $key == 'General')
                            <li class="nav-item ">
                                <a class="nav-link text-capitalize {{ $active }}" id="pills-{{ ($key) }}-tab-googledrive" data-bs-toggle="pill" href="#pills-{{ ($key) }}-googledrive" role="tab" aria-controls="pills-{{ ($key) }}-googledrive" aria-selected="true">{{ Module_Alias_Name($key) }}</a>
                            </li>
                            @php
                                $active = '';
                            @endphp
                        @endif
                    @endforeach
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    @php
                        $active1 = 'active';
                    @endphp
                    @foreach ($google_drive_modules as $key => $e_module)
                        @if((module_is_active($key)) || $key == 'General')
                            <div class="tab-pane fade {{ $active1 }} show" id="pills-{{ ($key) }}-googledrive" role="tabpanel" aria-labelledby="pills-{{ ($key) }}-tab-googledrive">
                                <div class="row">
                                    @foreach ($e_module as $sub_module)
                                    <div class="col-lg-4 col-sm-6 col-12 mb-3">
                                        <div class="rounded-1 card   list_colume_notifi p-3  h-100 mb-0">
                                            <div class="card-body d-flex align-items-center justify-content-between gap-2 p-0">
                                                <h6 class="mb-0">
                                                    <label for="{{ $sub_module }}" class="form-label mb-0">{{ ($sub_module) }}</label>
                                                </h6>
                                                <div class="form-check form-switch d-inline-block text-end">
                                                    <input type="hidden" name="google_drive[{{ $sub_module.'_drive' }}]" value="0" />
                                                    <input class="form-check-input" {{( isset($company_settings[$sub_module.'_drive']) && $company_settings[$sub_module.'_drive'] == true) ? 'checked' : ''}} id="google_drive" name="google_drive[{{ $sub_module.'_drive' }}]" type="checkbox" value="1">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @php
                                        $active1 = '';
                                    @endphp
                                @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card-footer text-end p-3">
            <button class="btn-submit btn btn-primary" type="submit">
                {{__('Save Changes')}}
            </button>
        </div>
        {{Form::close()}}
</div>
<script>
    function openGoogleDriveAuthenticationWindow() {
        // Open a new window for authentication
        var authenticationWindow = window.open('{{ route('auth.google') }}', '_blank', 'width=800,height=800');
    }
</script>
@endpermission
