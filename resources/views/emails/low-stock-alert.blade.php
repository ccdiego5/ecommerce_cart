<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Alert</title>
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
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #dc3545;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .alert-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .alert-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-box p {
            margin: 0;
            color: #856404;
        }
        .product-info {
            background-color: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .product-info h2 {
            margin-top: 0;
            color: #333;
            font-size: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .stock-warning {
            color: #dc3545;
            font-weight: bold;
            font-size: 18px;
        }
        .action-box {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .action-box strong {
            color: #004085;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }
        .product-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="alert-icon">⚠️</div>
            <h1>Low Stock Alert</h1>
        </div>

        <div class="content">
            <div class="alert-box">
                <p><strong>Warning:</strong> A product in your inventory is running low on stock and requires immediate attention.</p>
            </div>

            <div class="product-info">
                @if($product->image)
                    <img src="{{ $product->image }}" alt="{{ $product->name }}" class="product-image">
                @endif

                <h2>{{ $product->name }}</h2>

                <div class="info-row">
                    <span class="info-label">Product ID:</span>
                    <span class="info-value">{{ $product->formatted_public_id }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Current Stock:</span>
                    <span class="stock-warning">{{ $product->stock_quantity }} units</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Low Stock Threshold:</span>
                    <span class="info-value">{{ $product->low_stock_threshold }} units</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Price:</span>
                    <span class="info-value">${{ number_format($product->price, 2) }}</span>
                </div>

                @if($product->description)
                <div class="info-row">
                    <span class="info-label">Description:</span>
                    <span class="info-value">{{ Str::limit($product->description, 100) }}</span>
                </div>
                @endif
            </div>

            <div class="action-box">
                <p><strong>Action Required:</strong> Please restock this product as soon as possible to avoid stockouts and potential lost sales.</p>
            </div>

            <p style="margin-top: 30px; color: #666;">
                This notification was triggered because the product's stock quantity 
                ({{ $product->stock_quantity }}) has fallen to or below the low stock threshold 
                ({{ $product->low_stock_threshold }}).
            </p>
        </div>

        <div class="footer">
            <p>This is an automated notification from {{ config('app.name') }}.</p>
            <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        </div>
    </div>
</body>
</html>

