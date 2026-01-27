@extends('layouts.main')

@section('page-title')
    {{ __('Manage Clients') }}
@endsection

@section('page-breadcrumb')
    {{ __('Clients') }}
@endsection

@section('page-action')
@permission('user reset password')
    <div>
        {{-- Additional actions can go here --}}
    </div>
@endpermission
@endsection


@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card mt-2">
            <div class="card-body">
                {{-- Investments Table --}}
                <div class="table-responsive mt-4">
                    <table class="table table-striped" id="investments-table">
                        <thead>
                            <tr>
                                <th>{{ __('S/N') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('House') }}</th>
                                <th>{{ __('Currency') }}</th>
                                <th>{{ __('Amount Agreed') }}</th>
                                <th>{{ __('Amount Paid') }}</th>
                                <th>{{ __('Land (Rec)') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Phone Number') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clients as $client)
                                <tr>
                                    <td>{{ $client->serial_number }}</td>
                                    <td>{{ $client->name }}</td>
                                    <td>{{ $client->house }}</td>
                                    <td>{{ $client->currency ?? 'NGN' }}</td>
                                    <td>{{ number_format($client->amount_agreed, 2) }}</td>
                                    <td>{{ number_format($client->amount_paid, 2) }}</td>
                                    <td>{{ number_format($client->land_rec, 2) }}</td>
                                    <td>{{ $client->email ?? 'N/A' }}</td>
                                    <td>{{ $client->phone_number }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <!-- Custom CSS for DataTables -->
    <style>
        /* Table Styling */
        #investments-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 12px;
        }

        #investments-table thead th {
            background-color: #2c3e50;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            padding: 12px;
            border-bottom: 2px solid #1a252f;
        }

        #investments-table tbody tr {
            transition: background-color 0.2s ease;
        }

        #investments-table tbody tr:hover {
            background-color: #f1f5f9;
        }

        #investments-table tbody td {
            padding: 12px;
            font-size: 0.95rem;
            color: #34495e;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            #investments-table thead th,
            #investments-table tbody td {
                font-size: 0.85rem;
                padding: 8px;
            }
        }

        /* DataTables controls */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 15px;
            color: #34495e;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 5px;
            font-size: 0.9rem;
        }

        /* Buttons styling */
        .dt-buttons .btn {
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 8px 12px;
            margin-right: 5px;
            transition: background-color 0.3s ease;
        }

        .dt-buttons .btn:hover {
            background-color: #2980b9;
        }

        /* Pagination */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            background: #3498db;
            color: #fff !important;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            margin: 0 2px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #2980b9;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #2c3e50;
        }

        /* Info text */
        .dataTables_wrapper .dataTables_info {
            font-size: 0.9rem;
            color: #34495e;
            padding-top: 10px;
        }
    </style>
@endpush

@push('scripts')
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <!-- DataTables Responsive -->
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.js"></script>

    <script>
        $(document).ready(function () {
            $('#investments-table').DataTable({
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"B>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                buttons: [
                    { extend: 'copy', className: 'btn btn-sm' },
                    { extend: 'excel', className: 'btn btn-sm' },
                    { extend: 'csv', className: 'btn btn-sm' },
                    { extend: 'pdf', className: 'btn btn-sm' },
                    { extend: 'print', className: 'btn btn-sm' }
                ],
                pageLength: 25,
                responsive: true,
                language: {
                    search: "",
                    searchPlaceholder: "{{ __('Search...') }}",
                    lengthMenu: "{{ __('Show _MENU_ entries') }}",
                    info: "{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}",
                    paginate: {
                        first: "{{ __('First') }}",
                        last: "{{ __('Last') }}",
                        next: "{{ __('Next') }}",
                        previous: "{{ __('Previous') }}"
                    }
                },
                order: [[0, 'asc']], // Sort by S/N (serial_number) by default
                columnDefs: [
                    { targets: [3, 4, 5], className: 'text-right' }, // Right-align Amount Agreed, Amount Paid, Land (Rec)
                    // { targets: [9], className: 'text-center' }
                ]
            });
        });
    </script>
@endpush