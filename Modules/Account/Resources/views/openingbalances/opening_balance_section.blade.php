<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Opening Balance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff8f0;
            color: #333;
        }

        .header {
            background-color: #ff7f00;
            color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .table thead {
            background-color: #ff7f00;
            color: white;
        }

        .table tbody tr:hover {
            background-color: #fff0e0;
        }

        .summary-box {
            background-color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="header">
            <h2 class="mb-0">Opening Balance</h2>
        </div>

        @if(!empty($openingBalances))
            <div class="summary-box">
                <p><strong>Opening Balance ID:</strong> {{ $openingBalances['opening_balance_id'] }}</p>
                <p><strong>Date:</strong> {{ $openingBalances['date'] }}</p>
                <p><strong>Total:</strong> {{ number_format($openingBalances['total'], 2) }}</p>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Account Name</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Currency</th>
                            <th>Exchange Rate</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($openingBalances['accounts'] as $account)
                            <tr>
                                <td>{{ $account['account_name'] }}</td>
                                <td>{{ ucfirst($account['debit_or_credit']) }}</td>
                                <td>{{ number_format($account['amount'], 2) }}</td>
                                <td>{{ $account['currency_code'] }}</td>
                                <td>{{ $account['exchange_rate'] }}</td>
                                <td>{{ $account['location_name'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-warning">No opening balance data found.</div>
        @endif
    </div>
</body>
</html>
