<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Livestock Express - Transaction Report</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; margin: 0; padding: 20px; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0F5132; padding-bottom: 15px; }
        .header h1 { color: #0F5132; margin: 0; font-size: 22px; text-transform: uppercase; letter-spacing: 0.5px; }
        .header p { margin: 4px 0; color: #64748b; font-size: 11px; }
        .meta-box { display: flex; justify-content: space-between; margin-bottom: 20px; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: left; }
        th { background-color: #0F5132; color: #ffffff; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:nth-child(even) { background-color: #f8fafc; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="background: #0F5132; color: white; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: bold;">Print / Save as PDF</button>
        <a href="{{ route('admin.transactions') }}" style="margin-left: 10px; text-decoration: none; color: #64748b;">Back to Admin</a>
    </div>

    <div class="header">
        <h1>City Veterinary Office — Alaminos City, Pangasinan</h1>
        <p><strong>Livestock Express</strong> | Official Marketplace Transaction & Sales Audit Report</p>
        <p>Generated on: {{ $generatedAt }}</p>
    </div>

    <div class="meta-box">
        <div><strong>Total Records:</strong> {{ count($transactions) }}</div>
        <div><strong>Total Completed Sales Revenue:</strong> ₱{{ number_format($totalRevenue, 2) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Reference ID</th>
                <th>Livestock Name</th>
                <th>Buyer / Seller</th>
                <th>Amount (PHP)</th>
                <th>Status</th>
                <th>Date / Time</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $txn)
                <tr>
                    <td class="font-bold">#{{ substr($txn['id'] ?? ($txn['orderId'] ?? 'N/A'), 0, 8) }}</td>
                    <td>{{ $txn['livestockName'] ?? 'Livestock Item' }}</td>
                    <td>
                        {{ $txn['buyerName'] ?? 'Buyer' }} <br>
                        <span style="color: #64748b; font-size: 10px;">Seller: {{ $txn['sellerName'] ?? 'Seller' }}</span>
                    </td>
                    <td class="font-bold" style="color: #0F5132;">₱{{ number_format((float)($txn['totalAmount'] ?? ($txn['amount'] ?? 0)), 2) }}</td>
                    <td>{{ $txn['orderStatus'] ?? ($txn['status'] ?? 'Pending') }}</td>
                    <td>{{ $txn['createdAt'] ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b;">No transaction records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Livestock Express Agri-Tech Platform &bull; Certified Official System Report</p>
    </div>

</body>
</html>