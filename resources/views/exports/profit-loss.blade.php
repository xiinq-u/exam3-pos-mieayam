<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
        h1 { margin-bottom: 4px; font-size: 20px; }
        .period { color: #4b5563; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { padding: 9px; border-bottom: 1px solid #d1d5db; }
        th { background: #f3f4f6; text-align: left; }
        .number { text-align: right; }
        .total td { font-weight: bold; border-top: 2px solid #374151; }
    </style>
</head>
<body>
    <h1>Laporan Laba-Rugi</h1>
    <p class="period">Periode {{ $report['start_date'] }} sampai {{ $report['end_date'] }}</p>
    <table>
        <thead><tr><th>Keterangan</th><th class="number">Nilai (Rp)</th></tr></thead>
        <tbody>
            <tr><td>Penjualan</td><td class="number">{{ number_format($report['sales'], 0, ',', '.') }}</td></tr>
            <tr><td>Refund</td><td class="number">({{ number_format($report['refunds'], 0, ',', '.') }})</td></tr>
            <tr><td>Pemasukan lain</td><td class="number">{{ number_format($report['other_income'], 0, ',', '.') }}</td></tr>
            <tr><td>Harga pokok penjualan</td><td class="number">({{ number_format($report['cost_of_goods_sold'], 0, ',', '.') }})</td></tr>
            <tr class="total"><td>Laba kotor</td><td class="number">{{ number_format($report['gross_profit'], 0, ',', '.') }}</td></tr>
            <tr><td>Pengeluaran</td><td class="number">({{ number_format($report['expenses'], 0, ',', '.') }})</td></tr>
            <tr class="total"><td>Laba bersih</td><td class="number">{{ number_format($report['net_profit'], 0, ',', '.') }}</td></tr>
        </tbody>
    </table>
</body>
</html>
