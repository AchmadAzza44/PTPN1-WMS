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
        .no-data { text-align: center; padding: 20px; color: #888; font-style: italic; }
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

        @forelse($reports as $index => $r)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td style="text-align:left">{{ $r->quality_type }}</td>
            <td>{{ $r->opening_palet }}</td>
            <td>{{ $r->inbound_palet }}</td>
            <td>{{ $r->outbound_palet }}</td>
            <td>{{ $r->closing_palet }}</td>
            <td class="bg-gray">{{ number_format($r->opening_kg, 0, ',', '.') }}</td>
            <td class="bg-gray">{{ number_format($r->inbound_kg, 0, ',', '.') }}</td>
            <td class="bg-gray">{{ number_format($r->outbound_kg, 0, ',', '.') }}</td>
            <td class="bg-gray">{{ number_format($r->closing_kg, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="no-data">Belum ada data stok untuk tanggal ini.</td>
        </tr>
        @endforelse

        @if($reports->count() > 0)
        <tr class="bg-gray" style="font-weight: bold;">
            <td colspan="2">TOTAL</td>
            <td>{{ $reports->sum('opening_palet') }}</td>
            <td>{{ $reports->sum('inbound_palet') }}</td>
            <td>{{ $reports->sum('outbound_palet') }}</td>
            <td>{{ $reports->sum('closing_palet') }}</td>
            <td>{{ number_format($reports->sum('opening_kg'), 0, ',', '.') }}</td>
            <td>{{ number_format($reports->sum('inbound_kg'), 0, ',', '.') }}</td>
            <td>{{ number_format($reports->sum('outbound_kg'), 0, ',', '.') }}</td>
            <td>{{ number_format($reports->sum('closing_kg'), 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>

    <table class="signature-table" style="width: 100%">
        <tr>
            <td align="center">Mengetahui,<br>Koordinator PMG P. Baai Bkl.<br><br><br><br><b>{{ $koordinator }}</b></td>
            <td align="center">Krani IPMG P. Baai Bengkulu<br><br><br><br><b>{{ $krani }}</b></td>
        </tr>
    </table>
</body>
</html>