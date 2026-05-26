<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Financial Report — Golden Crumb</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            padding: 40px;
            background: white;
            color: #1e293b;
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .print-header h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .print-header p {
            color: #64748b;
            font-size: 14px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .summary-card {
            border: 1px solid #e2e8f0;
            padding: 20px;
            border-radius: 8px;
        }
        
        .summary-card p:first-child {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 8px;
        }
        
        .summary-card p:last-child {
            font-size: 24px;
            font-weight: bold;
        }
        
        .chart-section {
            border: 1px solid #e2e8f0;
            padding: 24px;
            border-radius: 8px;
            margin-bottom: 40px;
        }
        
        .chart-section h2 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .orders-section {
            border: 1px solid #e2e8f0;
            padding: 24px;
            border-radius: 8px;
        }
        
        .orders-section h2 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        th {
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            color: #64748b;
        }
        
        td {
            font-size: 14px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-emerald-600 {
            color: #059669;
            font-weight: 600;
        }
        
        @media print {
            body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="print-header">
        <h1>Financial Report</h1>
        <p>Generated on {{ now()->format('F j, Y H:i') }}</p>
        <p style="margin-top: 4px; font-size: 12px;">
            @if ($period === 'today')
                Period: Today ({{ now()->format('F j, Y') }})
            @elseif ($period === 'week')
                Period: This Week ({{ now()->startOfWeek()->format('M j') }} - {{ now()->endOfWeek()->format('M j, Y') }})
            @elseif ($period === 'month')
                Period: This Month ({{ now()->format('F Y') }})
            @elseif ($period === 'year')
                Period: This Year ({{ now()->format('Y') }})
            @elseif ($period === 'custom' && $startDate && $endDate)
                Period: {{ \Carbon\Carbon::parse($startDate)->format('M j, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M j, Y') }}
            @endif
        </p>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <p>Total Income</p>
            <p>${{ number_format($totalIncome, 2) }}</p>
        </div>
        <div class="summary-card">
            <p>Total Outcome</p>
            <p>${{ number_format($totalOutcome, 2) }}</p>
        </div>
        <div class="summary-card">
            <p>Net Profit</p>
            <p>${{ number_format($netProfit, 2) }}</p>
        </div>
        <div class="summary-card">
            <p>Total Orders</p>
            <p>{{ $totalOrders }}</p>
        </div>
    </div>

    <div class="summary-grid" style="grid-template-columns: repeat(2, 1fr);">
        <div class="summary-card">
            <p>Average Order Value</p>
            <p>${{ number_format($averageOrderValue, 2) }}</p>
        </div>
    </div>

    <div class="orders-section">
        <h2>Recent Orders</h2>
        @if ($orders->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Table</th>
                        <th>Date</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->cafeTable->name }}</td>
                            <td>{{ $order->paid_at ? $order->paid_at->format('M d, Y H:i') : '-' }}</td>
                            <td class="text-right text-emerald-600">${{ number_format($order->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="text-align: center; padding: 32px; color: #64748b;">No orders in this period</p>
        @endif
    </div>

    <script>
        window.onload = function() {
            window.print();
            window.onafterprint = function() {
                window.close();
            };
        };
    </script>
</body>
</html>
