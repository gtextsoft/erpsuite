@extends('layouts.main')

@section('page-title')
    {{ __('Invoice Summary') }}
@endsection

@section('page-breadcrumb')
    {{ __('Invoices') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="invoices">
                            <thead>
                                <tr>
                                    <th>{{ __('Invoice Number') }}</th>
                                    <th>{{ __('Customer Name') }}</th>
                                    <th>{{ __('Company') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $invoi)
                                    <tr class="font-style">
                                        <td>{{ $invoi['invoice_number'] ?? '' }}</td>
                                        <td>{{ $invoi['customer_name'] ?? '' }}</td>
                                        <td>{{ $invoi['company_name'] ?? '' }}</td>
                                        <td>{{ ucfirst($invoi['status'] ?? '') }}</td>
                                        <td>{{ $invoi['date'] ?? '' }}</td>
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
