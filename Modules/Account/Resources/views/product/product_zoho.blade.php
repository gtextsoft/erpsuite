@extends('layouts.main')

@section('page-title')
    {{ __('Products') }}
@endsection

@section('page-breadcrumb')
    {{ __('Products') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    @if(!empty($products) && count($products))
                        <div class="table-responsive">
                            <table class="table mb-0 pc-dt-simple" id="products">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('SKU') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Selling Price') }} (₦)</th>
                                        <th>{{ __('Cost Price') }} (₦)</th>
                                        <th>{{ __('Unit') }}</th>
                                        <th>{{ __('Stock') }}</th>
                                        <th>{{ __('Sales Account') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $index => $product)
                                        <tr class="font-style">
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $product['name'] }}</td>
                                            <td>{{ $product['sku'] ?? '-' }}</td>
                                            <td>{{ $product['description'] ?? '-' }}</td>
                                            <td>{{ number_format($product['rate'], 2) }}</td>
                                            <td>{{ number_format($product['purchase_rate'], 2) }}</td>
                                            <td>{{ $product['unit'] ?? '' ?: '-' }}</td>
                                            <td>{{ isset($product['stock_on_hand']) ? number_format($product['stock_on_hand'], 0) : '-' }}</td>
                                            <td>{{ $product['account_name'] ?: '-' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $product['status'] == 'active' ? 'success' : 'danger' }} text-uppercase">{{ $product['status'] }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning">{{ __('No product records found.') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    {!! session('debug') !!}
@endsection