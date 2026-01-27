@php
    $company_setting = getCompanyAllSetting();
@endphp

<div class="card" id="zoom-sidenav">
    {{ Form::open(['route' => 'zoom-meeting.setting.store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
    <div class="card-header p-3">
        <h5>{{ __('Zoom Metting') }}</h5>
    </div>
    <div class="card-body p-3 pb-0">
        <div class="row">
            <div class="form-group col-sm-6">
                <label class="form-label ">{{ __('Zoom Account ID') }}</label> <br>
                <input class="form-control" placeholder="{{ __('Zoom Account ID') }}" name="zoom_account_id" type="text"
                    value="{{ isset($company_setting['zoom_account_id']) ? $company_setting['zoom_account_id'] : ''  }}">
            </div>
            <div class="form-group col-sm-6">
                <label class="form-label ">{{ __('Zoom Client ID') }}</label> <br>
                <input class="form-control" placeholder="{{ __('Zoom Client ID') }}" name="zoom_client_id" type="text"
                    value="{{ isset($company_setting['zoom_client_id']) ? $company_setting['zoom_client_id'] :'' }}">
            </div>
            <div class="form-group col-sm-6">
                <label class="form-label ">{{ __('Zoom Client Secret') }}</label> <br>
                <input class="form-control" placeholder="{{ __('Zoom Client Secret') }}" name="zoom_client_secret" type="text"
                    value="{{ isset($company_setting['zoom_client_secret']) ? $company_setting['zoom_client_secret'] :'' }}">
            </div>
        </div>
    </div>
    <div class="card-footer text-end p-3">
        <input class="btn btn-print-invoice  btn-primary" type="submit" value="{{ __('Save Changes') }}">
    </div>
    {{ Form::close() }}
</div>
