@extends('layouts.main')
@section('page-title')
    {{ __('Manage Quiz Setup') }}
@endsection
@section('page-breadcrumb')
    {{ __('Quiz Setup') }}
@endsection
@push('css')
     <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
@endpush
@section('page-action')
    <div class="d-flex">
        @permission('quizcategories create')
            <a data-size="md" data-url="{{ route('quiz.categories.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip"
                title="{{ __('Create') }}" data-title="{{ __('Create Quiz Categories') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive overflow_hidden">
                        {{ $dataTable->table(['width' => '100%']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
     <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    {{ $dataTable->scripts() }}
@endpush
