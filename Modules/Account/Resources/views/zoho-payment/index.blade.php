@extends('layouts.main')

@section('page-title')
    {{ __('Customer Payments') }}
@endsection

@section('page-breadcrumb')
    {{ __('Payments') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    @if(!empty($payments) && count($payments))
                        <div class="table-responsive">
                            <table class="table mb-0 pc-dt-simple" id="payments">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Payment No.') }}</th>
                                        <th>{{ __('Customer') }}</th>
                                        <th>{{ __('Invoice No.') }}</th>
                                        <th>{{ __('Amount') }} (₦)</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Reference') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Account Name') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $index => $payment)
                                        <tr class="font-style">
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $payment['payment_number'] }}</td>
                                            <td>{{ $payment['customer_name'] }}</td>
                                            <td>{{ $payment['invoice_numbers'] }}</td>
                                            <td>{{ number_format($payment['amount'], 2) }}</td>
                                            <td>{{ \Carbon\Carbon::parse($payment['date'])->format('d M Y') }}</td>
                                            <td>{{ $payment['reference_number'] }}</td>
                                            <td>{{ $payment['description'] ?: '-' }}</td>
                                            <td>{{ $payment['account_name'] }}</td>
                                            <td>
                                                <span class="badge bg-success text-uppercase">{{ $payment['payment_status'] }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning">{{ __('No payment records found.') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
