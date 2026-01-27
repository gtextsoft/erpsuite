@extends('layouts.main')

@section('page-title')
    {{ __('Journal Entries') }}
@endsection

@section('page-breadcrumb')
    {{ __('Journal Entries') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    @if(!empty($journals) && count($journals))
                        <div class="table-responsive">
                            <table class="table mb-0 pc-dt-simple" id="journals">
                                <thead>
                                    <tr>
                                        <th>{{ __('Journal ID') }}</th>
                                        <th>{{ __('Entry Number') }}</th>
                                        <th>{{ __('Reference Number') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Branch Name') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Notes') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Total') }}</th>
                                        <th>{{ __('Created By') }}</th>
                                        <th>{{ __('Last Modified') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($journals as $journal)
                                        <tr class="font-style">
                                            <td>{{ $journal['journal_id'] }}</td>
                                <td>{{ $journal['entry_number'] }}</td>
                                <td>{{ $journal['reference_number'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($journal['journal_date'])->format('Y-m-d') }}</td>
                                <td>{{ $journal['branch_name'] }}</td>
                                <td>{{ ucfirst($journal['status']) }}</td>
                                <td>{{ $journal['notes'] }}</td>
                                <td>{{ ucfirst($journal['journal_type']) }}</td>
                                <td>&#8358;{{ number_format($journal['total'], 2) }}</td>
                                <td>{{ $journal['created_by_name'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($journal['last_modified_time'])->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning">{{ __('No journal entries found.') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection