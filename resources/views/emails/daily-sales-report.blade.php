<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .header p {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            padding: 30px 20px;
            background-color: #f8f9fa;
        }
        .stat-box {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 30px 20px;
        }
        .section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: white;
        }
        th {
            background-color: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 14px;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .amount {
            font-weight: bold;
            color: #28a745;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }
        .summary-box {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .summary-box p {
            margin: 5px 0;
        }
        @media only screen and (max-width: 600px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            .stat-value {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Daily Sales Report</h1>
            <p>{{ $salesData['date'] }}</p>
        </div>

        <div class="stats-container">
            <div class="stat-box">
                <div class="stat-value">{{ $salesData['total_orders'] }}</div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">${{ number_format($salesData['total_revenue'], 2) }}</div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ $salesData['total_products_sold'] }}</div>
                <div class="stat-label">Products Sold</div>
            </div>
        </div>

        <div class="content">
            @if($salesData['total_orders'] > 0)
                <div class="summary-box">
                    <p><strong>Summary:</strong> Today you received {{ $salesData['total_orders'] }} {{ Str::plural('order', $salesData['total_orders']) }} totaling ${{ number_format($salesData['total_revenue'], 2) }} in revenue, with {{ $salesData['total_products_sold'] }} {{ Str::plural('product', $salesData['total_products_sold']) }} sold.</p>
                </div>

                <h2 class="section-title">Order Details</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th class="text-center">Items</th>
                            <th class="text-right">Amount</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salesData['orders'] as $order)
                            <tr>
                                <td><strong>{{ $order->order_number }}</strong></td>
                                <td>{{ $order->user->name }}</td>
                                <td class="text-center">{{ $order->orderItems->count() }}</td>
                                <td class="text-right amount">${{ number_format($order->total_amount, 2) }}</td>
                                <td>{{ $order->completed_at->format('g:i A') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #e7f3ff; font-weight: bold;">
                            <td colspan="3">TOTAL</td>
                            <td class="text-right amount">${{ number_format($salesData['total_revenue'], 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <h2 class="section-title">Products Sold Today</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th class="text-center">Quantity Sold</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Total Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salesData['products_summary'] as $product)
                            <tr>
                                <td><strong>{{ $product['product_name'] }}</strong></td>
                                <td class="text-center">{{ $product['quantity'] }}</td>
                                <td class="text-right">${{ number_format($product['price'], 2) }}</td>
                                <td class="text-right amount">${{ number_format($product['total'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #e7f3ff; font-weight: bold;">
                            <td>TOTAL</td>
                            <td class="text-center">{{ $salesData['total_products_sold'] }}</td>
                            <td></td>
                            <td class="text-right amount">${{ number_format($salesData['total_revenue'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <div style="margin-top: 30px; padding: 15px; background-color: #d1ecf1; border-left: 4px solid #0c5460; border-radius: 4px;">
                    <p style="margin: 0; color: #0c5460;">
                        <strong>📈 Performance Note:</strong> 
                        @if($salesData['total_orders'] > 10)
                            Great job! You had {{ $salesData['total_orders'] }} orders today.
                        @elseif($salesData['total_orders'] > 5)
                            Good day with {{ $salesData['total_orders'] }} orders.
                        @else
                            You received {{ $salesData['total_orders'] }} {{ Str::plural('order', $salesData['total_orders']) }} today.
                        @endif
                    </p>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>No Sales Today</h3>
                    <p>There were no completed sales on {{ $salesData['date'] }}.</p>
                </div>
            @endif
        </div>

        <div class="footer">
            <p><strong>This is an automated daily report from {{ config('app.name') }}</strong></p>
            <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
            <p style="margin-top: 10px; font-size: 11px;">
                This report includes all orders with status "completed" that were finalized on {{ $salesData['date'] }}.
            </p>
        </div>
    </div>
</body>
</html>

