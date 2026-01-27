@extends('layouts.main')

@section('page-title')
    {{ __('Chart of Accounts') }}
@endsection

@section('page-breadcrumb')
    {{ __('Chart of Accounts') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    @if(!empty($accounts) && count($accounts))
                        <div class="table-responsive">
                            <table class="table mb-0 pc-dt-simple" id="accounts">
                                <thead>
                                    <tr>
                                        <th>{{ __('Account ID') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('User Created') }}</th>
                                        <th>{{ __('System Account') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Created Time') }}</th>
                                        <th>{{ __('Last Modified') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($accounts as $account)
                                        <tr class="font-style">
                                             <td>{{ $account['account_id'] }}</td>
                                <td>{{ $account['account_name'] }}</td>
                                <td>{{ $account['account_type'] }}</td>
                                <td>{{ $account['description'] ?? '-' }}</td>
                                <td>{{ $account['is_user_created'] ? 'Yes' : 'No' }}</td>
                                <td>{{ $account['is_system_account'] ? 'Yes' : 'No' }}</td>
                                <td>{{ $account['is_active'] ? 'Active' : 'Inactive' }}</td>
                                <td>{{ \Carbon\Carbon::parse($account['created_time'])->format('Y-m-d H:i') }}</td>
                                <td>{{ \Carbon\Carbon::parse($account['last_modified_time'])->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning">{{ __('No accounts found.') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection