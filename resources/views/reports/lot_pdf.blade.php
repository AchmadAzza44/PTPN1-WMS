<!DOCTYPE html>
<html>
<head>
    <title>Laporan Daftar Stok Lot dan Palet</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 20px; }
        .header { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 20px; }
        .sub-header { text-align: center; font-weight: normal; margin-top: -15px; margin-bottom: 20px; font-size: 11px; }
        .main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .main-table th, .main-table td { border: 1px solid black; padding: 5px; text-align: center; }
        .signature-table { width: 100%; margin-top: 40px; }
        .bg-gray { background-color: #f2f2f2; }
        .no-data { text-align: center; padding: 20px; color: #888; font-style: italic; }
        .status-tersedia { color: green; font-weight: bold; }
        .status-keluar { color: dimgray; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        LAPORAN DAFTAR STOK LOT DAN PALET (REAL-TIME)<br>
        IPMG P. BAAI BENGKULU
    </div>
    <div class="sub-header">
        Periode: {{ $start_date }} s.d. {{ $end_date }}
    </div>

    <table class="main-table">
        <tr class="bg-gray">
            <th>NO</th>
            <th>TANGGAL INBOUND</th>
            <th>JENIS MUTU</th>
            <th>NO. LOT</th>
            <th>NO. PALET / FDF</th>
            <th>BERAT (KG)</th>
            <th>STATUS</th>
            <th>TANGGAL OUTBOUND</th>
        </tr>

        @php $no = 1; @endphp
        @forelse($lots as $lot)
            @foreach($lot->details as $detail)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ \Carbon\Carbon::parse($lot->inbound_at)->format('d-m-Y H:i') }}</td>
                <td>{{ $lot->quality_type }}</td>
                <td>{{ $lot->lot_number }}</td>
                <td>
                    @if($detail->fdf_number || $detail->pallet_number)
                        {{ $detail->fdf_number ?: $detail->pallet_number }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ number_format($detail->gross_weight_kg ?? $detail->net_weight_kg, 2, ',', '.') }}</td>
                <td>
                    @if($detail->net_weight_kg > 0)
                        <span class="status-tersedia">Tersedia</span>
                    @else
                        <span class="status-keluar">Keluar</span>
                    @endif
                </td>
                <td>
                    @if($detail->net_weight_kg == 0 && $lot->outbound_at)
                        {{ \Carbon\Carbon::parse($lot->outbound_at)->format('d-m-Y H:i') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        @empty
        <tr>
            <td colspan="8" class="no-data">Tidak ada data stok pada periode ini.</td>
        </tr>
        @endforelse
    </table>

    <table class="signature-table" style="width: 100%">
        <tr>
            <td align="center">Mengetahui,<br>Koordinator PMG P. Baai Bkl.<br><br><br><br><b>{{ $koordinator }}</b></td>
            <td align="center">Krani IPMG P. Baai Bengkulu<br><br><br><br><b>{{ $krani }}</b></td>
        </tr>
    </table>
</body>
</html>
