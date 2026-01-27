@extends('layouts.main')

@section('page-title')
    {{ __('Expense Records') }}
@endsection

@section('page-breadcrumb')
    {{ __('Expenses') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    @if(!empty($expenses) && count($expenses))
                        <div class="table-responsive">
                            <table class="table mb-0 pc-dt-simple" id="expenses">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Account') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Customer') }}</th>
                                        <th>{{ __('Total') }} ({{ $expenses[0]->currency_code ?? 'Currency' }})</th>
                                        <th>{{ __('Billable') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Location') }}</th>
                                        <th>{{ __('Created Time') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenses as $expense)
                                        <tr class="font-style">
                                             <td>{{ $expense['date'] }}</td>
                                <td>{{ $expense['account_name'] }}</td>
                                <td>{{ $expense['description'] }}</td>
                                <td>{{ $expense['customer_name'] }}</td>
                                <td>{{ number_format($expense['total'], 2) }}</td>
                                <td>{{ $expense['is_billable'] ? 'Yes' : 'No' }}</td>
                                <td>{{ ucfirst($expense['status']) }}</td>
                                <td>{{ $expense['location_name'] ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($expense['created_time'])->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning">{{ __('No expense records found.') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection