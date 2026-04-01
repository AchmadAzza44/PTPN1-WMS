<!DOCTYPE html>
<html>
<head>
    <title>Laporan Harian Mutasi Produksi</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 20px; }
        .header { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 20px; }
        .main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .main-table th, .main-table td { border: 1px solid black; padding: 5px; text-align: center; }
        .signature-table { width: 100%; margin-top: 40px; }
        .bg-gray { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        LAPORAN HARIAN MUTASI PRODUKSI KARET IPMG P. BAAI BENGKULU<br>
        TANGGAL: {{ $tanggal }}
    </div>

    <table class="main-table">
        <tr class="bg-gray">
            <th rowspan="2">NO</th>
            <th rowspan="2">JENIS MUTU</th>
            <th colspan="4">UNIT PAWI (PALET)</th>
            <th colspan="4">JUMLAH (KG)</th>
        </tr>
        <tr class="bg-gray">
            <th>SISA</th><th>MASUK</th><th>KELUAR</th><th>STOK</th>
            <th>SISA</th><th>MASUK</th><th>KELUAR</th><th>STOK</th>
        </tr>
        @foreach($reports as $index => $r)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td style="text-align:left">{{ $r->quality_type }}</td>
            <td>{{ $r->opening_stock }}</td>
            <td>{{ $r->inbound_total }}</td>
            <td>{{ $r->outbound_total }}</td>
            <td>{{ $r->closing_stock }}</td>
            <td class="bg-gray">{{ number_format($r->opening_stock * 1260) }}</td>
            <td class="bg-gray">{{ number_format($r->inbound_total * 1260) }}</td>
            <td class="bg-gray">{{ number_format($r->outbound_total * 1260) }}</td>
            <td class="bg-gray">{{ number_format($r->closing_stock * 1260) }}</td>
        </tr>
        @endforeach
    </table>

    <table class="signature-table" style="width: 100%">
        <tr>
            <td align="center">Mengetahui,<br>Koordinator PMG P. Baai Bkl.<br><br><br><br><b>{{ $koordinator }}</b></td>
            <td align="center">Krani IPMG P. Baai Bengkulu<br><br><br><br><b>{{ $krani }}</b></td>
        </tr>
    </table>
</body>
</html>