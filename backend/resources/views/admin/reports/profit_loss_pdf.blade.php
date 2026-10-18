<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit & Loss Statement</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #333; margin: 25px; }
        .header { border-bottom: 2px solid #6200EE; padding-bottom: 15px; margin-bottom: 20px; }
        .store-name { font-size: 20px; font-weight: bold; color: #111; }
        .report-title { font-size: 14px; font-weight: bold; color: #6200EE; margin-top: 5px; }
        .summary-box { width: 100%; margin-bottom: 25px; border-collapse: collapse; }
        .summary-box td { padding: 12px; background: #f8f9fc; border: 1px solid #e2e8f0; text-align: center; }
        .summary-title { font-size: 9px; text-transform: uppercase; color: #64748b; font-weight: bold; }
        .summary-val { font-size: 15px; font-weight: bold; color: #0f172a; margin-top: 4px; }
        table.statement-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table.statement-table th { background: #f1f5f9; padding: 10px 12px; text-align: left; font-size: 10px; text-transform: uppercase; border-bottom: 2px solid #cbd5e1; }
        table.statement-table td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        .row-sub { padding-left: 24px; color: #64748b; }
        .row-highlight { background: #f8fafc; font-weight: bold; }
        .row-total { background: #eff6ff; font-weight: bold; font-size: 13px; border-top: 2px solid #3b82f6; border-bottom: 2px solid #3b82f6; }
        .text-right { text-align: right; }
        .footer { margin-top: 40px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="store-name">{{ $shop->name ?? 'POS Store' }}</div>
                    <div>{{ $shop->address }}</div>
                    <div>Phone: {{ $shop->phone }} | Tax Reg: {{ $shop->tax_number ?? 'N/A' }}</div>
                </td>
                <td class="text-right">
                    <div class="report-title">PROFIT & LOSS STATEMENT</div>
                    <div>Period: {{ $startDate }} to {{ $endDate }}</div>
                    <div>Generated: {{ date('Y-m-d H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Summary KPI Box -->
    <table class="summary-box">
        <tr>
            <td>
                <div class="summary-title">Total Revenue</div>
                <div class="summary-val">{{ $currencySymbol }}{{ number_format($totalRevenue, 2) }}</div>
            </td>
            <td>
                <div class="summary-title">Cost of Goods</div>
                <div class="summary-val" style="color: #dc2626;">{{ $currencySymbol }}{{ number_format($cogs, 2) }}</div>
            </td>
            <td>
                <div class="summary-title">Gross Profit</div>
                <div class="summary-val" style="color: #6200EE;">{{ $currencySymbol }}{{ number_format($grossProfit, 2) }}</div>
            </td>
            <td>
                <div class="summary-title">Net Profit Margin</div>
                <div class="summary-val" style="color: {{ $netProfit >= 0 ? '#16a34a' : '#dc2626' }};">
                    {{ $currencySymbol }}{{ number_format($netProfit, 2) }} ({{ $profitMargin }}%)
                </div>
            </td>
        </tr>
    </table>

    <!-- Accounting Statement Table -->
    <table class="statement-table">
        <thead>
            <tr>
                <th>Account Item / Description</th>
                <th class="text-right" style="width: 160px;">Amount ({{ $currencySymbol }})</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>1. Sales Revenue (Gross Invoiced)</strong></td>
                <td class="text-right"><strong>{{ $currencySymbol }}{{ number_format($totalRevenue, 2) }}</strong></td>
            </tr>
            <tr>
                <td class="row-sub">Less: Cost of Goods Sold (Purchase Cost of Goods Sold)</td>
                <td class="text-right" style="color: #dc2626;">- {{ $currencySymbol }}{{ number_format($cogs, 2) }}</td>
            </tr>
            <tr class="row-highlight">
                <td><strong>GROSS PROFIT</strong></td>
                <td class="text-right" style="color: #6200EE;"><strong>{{ $currencySymbol }}{{ number_format($grossProfit, 2) }}</strong></td>
            </tr>
            <tr>
                <td class="row-sub">Add: Other Operating Income</td>
                <td class="text-right" style="color: #16a34a;">+ {{ $currencySymbol }}{{ number_format($totalIncome, 2) }}</td>
            </tr>
            <tr>
                <td class="row-sub">Less: Operating Expenses (Rent, Utilities, Wages, Supplies)</td>
                <td class="text-right" style="color: #dc2626;">- {{ $currencySymbol }}{{ number_format($totalExpenses, 2) }}</td>
            </tr>
            <tr class="row-total">
                <td><strong>NET PROFIT / (LOSS)</strong></td>
                <td class="text-right" style="color: {{ $netProfit >= 0 ? '#16a34a' : '#dc2626' }};">
                    <strong>{{ $currencySymbol }}{{ number_format($netProfit, 2) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        This is an official computer-generated statement of {{ $shop->name ?? 'POS Store' }}. Generated on {{ date('Y-m-d H:i:s') }}.
    </div>
</body>
</html>
