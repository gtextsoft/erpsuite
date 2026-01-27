@extends('layouts.app')

@section('content')
    <h1>Invoices</h1>

    @if ($errors->any())
        <div style="color: red;">
            {{ $errors->first() }}
        </div>
    @endif

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Invoice Number</th>
                <th>Customer Name</th>
                <th>Company</th>
                <th>Status</th>
               
                <th>Date</th>
                <th>View Invoice</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice as $invoi)
                <tr>
                    <td>{{ $invoi['invoice_number'] }}</td>
                    <td>{{ $invoi['customer_name'] }}</td>
                    <td>{{ $invoi['company_name'] }}</td>
                    <td>{{ ucfirst($invoi['status']) }}</td>
                   
                    <td>{{ $invoi['date'] }}</td>
                    <td><a href="{{ $invoice['invoice_url'] }}" target="_blank">View</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
