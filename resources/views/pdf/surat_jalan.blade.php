<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Surat Jalan - {{ $shipment->id }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid black;
            padding-bottom: 10px;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
        }

        .document-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
            text-decoration: underline;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 3px;
            vertical-align: top;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
        }

        .items-table th {
            background-color: #f0f0f0;
        }

        .signatures {
            width: 100%;
            margin-top: 50px;
        }

        .signatures td {
            text-align: center;
            width: 33%;
            vertical-align: bottom;
            height: 80px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-name">PT PERKEBUNAN NUSANTARA I</div>
        <div>Pabrik Karet RSS P. Baai</div>
        <div class="document-title">SURAT PENGANTAR PENGIRIMAN (DO)</div>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%">No. Dokumen</td>
            <td width="35%">: DO/{{ date('Y') }}/{{ str_pad($shipment->id, 5, '0', STR_PAD_LEFT) }}</td>
            <td width="15%">Tanggal</td>
            <td width="35%">: {{ $shipment->created_at->format('d F Y') }}</td>
        </tr>
        <tr>
            <td>Tujuan</td>
            <td>: {{ $shipment->purchaseOrder->contract->buyer_name ?? 'Buyer Umum' }}</td>
            <td>No. Polisi</td>
            <td>: {{ $shipment->vehicle_plate }}</td>
        </tr>
        <tr>
            <td>Ekspedisi</td>
            <td>: {{ $shipment->transporter_name }}</td>
            <td>Pengemudi</td>
            <td>: {{ $shipment->driver_name }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">No. Lot / Batch</th>
                <th width="20%">Kualitas</th>
                <th width="25%">Keterangan</th>
                <th width="15%">Berat (Kg)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shipment->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->stockLot->lot_number }}</td>
                    <td>{{ $item->stockLot->quality_type }}</td>
                    <td>{{ $item->stockLot->description ?? '-' }}</td>
                    <td style="text-align: right;">{{ number_format($item->qty_loaded_kg, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL BERAT</td>
                <td style="text-align: right; font-weight: bold;">
                    {{ number_format($shipment->items->sum('qty_loaded_kg'), 0) }} Kg</td>
            </tr>
        </tfoot>
    </table>

    <table class="signatures">
        <tr>
            <td>
                Dibuat Oleh,<br><br><br><br>
                ( {{ strtoupper($shipment->krani_name ?? 'Krani Pengiriman') }} )
            </td>
            <td>
                Diperiksa Oleh,<br><br><br><br>
                ( Asisten Gudang )
            </td>
            <td>
                Diterima Oleh,<br><br><br><br>
                ( {{ $shipment->driver_name }} )
            </td>
        </tr>
    </table>
</body>

</html>