@extends('layouts.main')

@section('page-title')
    {{ __('Zoho Contacts') }}
@endsection

@section('page-breadcrumb')
    {{ __('Contacts') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    @if(!empty($contacts) && count($contacts))
                        <div class="table-responsive">
                            <table class="table mb-0 pc-dt-simple" id="contacts">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Contact Name') }}</th>
                                        <th>{{ __('Company') }}</th>
                                        <th>{{ __('Email') }}</th>
                                        <th>{{ __('Phone') }}</th>
                                        <th>{{ __('Mobile') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Created') }}</th>
                                        <th>{{ __('Last Modified') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contacts as $index => $contact)
                                        <tr class="font-style">
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $contact['contact_name'] }}</td>
                                            <td>{{ $contact['company_name'] ?: '-' }}</td>
                                            <td>{{ $contact['email'] ?: '-' }}</td>
                                            <td>{{ $contact['phone'] ?: '-' }}</td>
                                            <td>{{ $contact['mobile'] ?: '-' }}</td>
                                            <td>{{ ucfirst($contact['customer_sub_type']) }}</td>
                                            <td>{{ ucfirst($contact['status']) }}</td>
                                            <td>{{ $contact['created_time_formatted'] }}</td>
                                            <td>{{ $contact['last_modified_time_formatted'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning">{{ __('No contacts found.') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
