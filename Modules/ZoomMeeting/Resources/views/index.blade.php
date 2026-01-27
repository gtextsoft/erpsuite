@extends('layouts.main')
@section('page-title')
    {{ __('Manage Zoom Meeting') }}
@endsection
@section('page-breadcrumb')
    {{ __('Zoom Meeting') }}
@endsection
@push('css')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
@endpush
@section('page-action')
    <div class="d-flex">
        <a href="{{ route('zoom-meeting.calender') }}" class="btn btn-sm btn-primary me-2" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('Calendar View') }}">
            <i class="ti ti-calendar"></i>
        </a>
        @permission('zoommeeting create')
            <a href="#" class="btn btn-sm btn-primary me-2" data-size="lg" data-url="{{ route('zoom-meeting.create') }}"
                data-ajax-popup="true" data-title="{{ __('Create Zoom Meeting') }}" data-bs-toggle="tooltip"
                data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        {{ $dataTable->table(['width' => '100%']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/plugins/datepicker-full.min.js') }}"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    {{ $dataTable->scripts() }}
@endpush
