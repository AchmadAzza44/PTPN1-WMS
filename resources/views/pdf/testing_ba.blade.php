<!DOCTYPE html>
<html>
<head>
    <title>Test Print - Berita Acara</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; font-weight: bold; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        .footer { margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        BERITA ACARA PENYERAHAN BARANG (TESTING)
    </div>

    <p>Telah diserahkan barang kepada pembeli dengan rincian sebagai berikut:</p>

    <table>
        <tr>
            <th>No. Kontrak (SC)</th>
            <td>{{ $contract_no }}</td> </tr>
        <tr>
            <th>Nama Pembeli</th>
            <td>{{ $buyer_name }}</td> </tr>
        <tr>
            <th>No. PO / Order</th>
            <td>{{ $po_no }}</td> </tr>
    </table>

    <h3>Detail Barang Terkirim</h3>
    <table>
        <thead>
            <tr>
                <th>No. Lot</th>
                <th>Jenis Mutu</th>
                <th>Tonase (KG)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->lot_number }}</td> <td>{{ $item->quality_type }}</td>
                <td>{{ number_format($item->details->sum('net_weight_kg')) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Bengkulu, {{ date('d F Y') }} <br><br><br>
        ( _________________ ) <br>
        Krani Pawi
    </div>
</body>
</html>