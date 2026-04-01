<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>SJT - {{ $shipment->do_number_manual ?? $shipment->id }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 10mm 15mm 10mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
        }

        /* ===== HEADER ===== */
        .main-title {
            text-align: center;
            margin-bottom: 3px;
        }

        .main-title h2 {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
            text-decoration: underline;
        }

        .main-title .subtitle {
            font-size: 11px;
            font-weight: bold;
            margin: 2px 0 8px 0;
            text-decoration: underline;
        }

        /* ===== INFO FIELDS ===== */
        .info-section {
            width: 100%;
            border: none;
            margin-bottom: 8px;
        }

        .info-section td {
            border: none;
            padding: 1px 3px;
            font-size: 10px;
            vertical-align: top;
        }

        .info-section .label {
            width: 120px;
        }

        .info-section .sep {
            width: 10px;
        }

        /* ===== MAIN TABLE ===== */
        .sjt-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .sjt-table th,
        .sjt-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 9px;
            text-align: center;
            vertical-align: middle;
        }

        .sjt-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 9px;
        }

        .sjt-table td.left {
            text-align: left;
        }

        .sjt-table td.right {
            text-align: right;
        }

        .sjt-table tfoot td {
            font-weight: bold;
        }

        /* ===== KETERANGAN ===== */
        .keterangan {
            margin: 5px 0 3px 0;
            font-size: 9px;
        }

        .keterangan ul {
            margin: 2px 0;
            padding-left: 25px;
        }

        .keterangan li {
            margin-bottom: 1px;
        }

        /* ===== SIGNATURES ===== */
        .signature-section {
            width: 100%;
            border: none;
            margin-top: 8px;
        }

        .signature-section td {
            border: none;
            padding: 2px 5px;
            font-size: 10px;
            vertical-align: top;
        }

        .sig-left {
            width: 25%;
        }

        .sig-center {
            width: 25%;
            text-align: center;
        }

        .sig-right {
            width: 50%;
        }

        .sig-space {
            height: 40px;
        }

        .sig-name {
            font-weight: bold;
            text-decoration: underline;
        }

        /* Keterangan checklist table */
        .ket-table {
            border: none;
            font-size: 9px;
        }

        .ket-table td {
            border: none;
            padding: 0 3px;
            font-size: 9px;
        }
    </style>
</head>

<body>
    @php
        $dispatchDate = $shipment->dispatched_at ?? now();
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $bulanRomawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $bulanAngka = $dispatchDate->format('n');
        $tahun = $dispatchDate->format('Y');

        $contract = $shipment->purchaseOrder->contract ?? null;
        $po = $shipment->purchaseOrder ?? null;
        $buyerName = $contract->buyer_name ?? 'Pembeli';

        // Nomor SP
        $noSP = 'IPMG.Bkl/SP/' . str_pad($shipment->id, 3, '0', STR_PAD_LEFT) . '/' . $bulanRomawi[$bulanAngka] . '/' . $tahun;

        // Kumpulkan jenis mutu
        $qualityTypes = $shipment->items->map(function($item) {
            return $item->stockLot->quality_type ?? '-';
        })->unique()->values();

        $totalKg = $shipment->items->sum('qty_loaded_kg');
        $totalUnits = 0;
        foreach ($shipment->items as $item) {
            if ($item->stockLot && $item->stockLot->details) {
                $totalUnits += $item->stockLot->details->sum('quantity_unit');
            }
        }

        // Vehicle checklist (parse if needed)
        $checklist = $shipment->vehicle_checklist;
        if (is_string($checklist)) {
            $checklist = json_decode($checklist, true);
        }
        $checklist = $checklist ?? [];
    @endphp

    <!-- ===== HEADER TITLE ===== -->
    <div class="main-title">
        <h2>SISTEM JAMINAN TRANSPORTASI &nbsp; (SJT)</h2>
        <div class="subtitle">PENGIRIMAN BARANG DARI GUDANG PTPN 1, REGIONAL 7 - PULAU BAAI BENGKULU</div>
    </div>

    <!-- ===== INFO FIELDS ===== -->
    <table class="info-section">
        <tr>
            <td class="label">No.</td>
            <td class="sep">:</td>
            <td>{{ $noSP }}</td>
            <td style="width: 80px; text-align: right;">Tujuan</td>
            <td class="sep">:</td>
            <td>{{ $buyerName }}</td>
        </tr>
        <tr>
            <td class="label">Kontrak / Memo No</td>
            <td class="sep">:</td>
            <td>{{ $contract->contract_number ?? '-' }}</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="label">No DO</td>
            <td class="sep">:</td>
            <td>{{ $po->po_number ?? '-' }}</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <!-- ===== MAIN TABLE ===== -->
    <table class="sjt-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">No</th>
                <th rowspan="2" style="width: 110px;">No.Surat<br>Pengantar</th>
                <th rowspan="2" style="width: 45px;">Tahun<br>Produksi</th>
                <th rowspan="2" style="width: 55px;">Jenis<br>Mutu</th>
                <th rowspan="2" style="width: 75px;">Nomor Pallet/<br>Bali</th>
                <th colspan="2">Jumlah / Berat</th>
                <th rowspan="2" style="width: 75px;">Nomor<br>Polisi</th>
                <th rowspan="2" style="width: 190px;">Keterangan</th>
            </tr>
            <tr>
                <th style="width: 40px;">Plt/Bali</th>
                <th style="width: 55px;">Kg</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shipment->items as $index => $item)
                @php
                    $lot = $item->stockLot;
                    $details = $lot ? $lot->details : collect();
                    $palletNumbers = $details->pluck('fdf_number')->filter()->unique()->implode(', ');
                    $unitCount = $details->sum('quantity_unit');
                    $productionYear = $lot->production_year ?? ($lot->inbound_at ? date('Y', strtotime($lot->inbound_at)) : '-');
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="left">{{ $shipment->do_number_manual ?? $noSP }}</td>
                    <td>{{ $productionYear }}</td>
                    <td>{{ $lot->quality_type ?? '-' }}</td>
                    <td>{{ $palletNumbers ?: $lot->lot_number }}</td>
                    <td>{{ $unitCount > 0 ? $unitCount : '-' }}</td>
                    <td class="right">{{ number_format($item->qty_loaded_kg, 0, ',', '.') }}</td>
                    <td>{{ $shipment->vehicle_plate }}</td>
                    @if($index === 0)
                        <td rowspan="{{ $shipment->items->count() }}" class="left" style="vertical-align: top; font-size: 8px; padding: 3px;">
                            <table class="ket-table">
                                <tr><td>Keadaan Barang</td><td>:</td><td>{{ $checklist['keadaan_barang'] ?? 'Baik' }}</td></tr>
                                <tr><td>Terpal</td><td>:</td><td>{{ $checklist['terpal'] ?? 'Bagus' }}</td></tr>
                                <tr><td>Koyong</td><td>:</td><td>{{ $checklist['koyong'] ?? 'Sedang' }}</td></tr>
                                <tr><td>Tali</td><td>:</td><td>{{ $checklist['tali'] ?? 'Rapih' }}</td></tr>
                                <tr><td>Alas bak</td><td>:</td><td>{{ $checklist['alas_bak'] ?? 'Bersih' }}</td></tr>
                                <tr><td>Ban</td><td>:</td><td>{{ $checklist['ban'] ?? '-' }}</td></tr>
                                <tr><td>Cuaca</td><td>:</td><td>{{ $shipment->weather_condition ?? 'Terang' }}</td></tr>
                            </table>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right;">Jumlah</td>
                <td>{{ $totalUnits > 0 ? $totalUnits : '-' }}</td>
                <td class="right">{{ number_format($totalKg, 0, ',', '.') }}</td>
                <td colspan="2" style="text-align: left; font-size: 8px;">
                    @if($totalUnits > 0)
                        {{ $totalUnits }} Pallet x @ {{ $totalUnits > 0 ? number_format($totalKg / $totalUnits, 0, ',', '.') : '-' }} kg/palet
                    @endif
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- ===== KETERANGAN ===== -->
    <div class="keterangan">
        <strong>Keterangan :</strong>
        <ul>
            <li>Barang dikirim dalam keadaan baik (tidak mentah,tidak basah dan tidak berjamur)</li>
            <li>Jumlah barang dalam keadaan cukup (dicek oleh Ekspedisi dan supir)</li>
        </ul>
    </div>

    <!-- ===== TANDA TANGAN ===== -->
    <table class="signature-section">
        <tr>
            <td class="sig-left">
                Bengkulu, {{ $dispatchDate->format('d') }} {{ $bulanIndo[$bulanAngka] }} {{ $tahun }}<br>
                Diserahkan Oleh,<br>
                Koordinator/ Manager Unit<br>
                Wilayah Bengkulu
            </td>
            <td class="sig-center">
                <br>
                Dibuat oleh<br>
                Bagian Gudang
            </td>
            <td class="sig-right">
                <br>
                <strong>Diserahkan kepada</strong><br>
                <table class="ket-table" style="margin-top: 35px;">
                    <tr>
                        <td>1. Ekspedisi</td>
                        <td>:</td>
                        <td>___________________</td>
                    </tr>
                    <tr><td colspan="3" style="height: 10px;"></td></tr>
                    <tr>
                        <td>2. Sopir</td>
                        <td>:</td>
                        <td>{{ $shipment->driver_name }}</td>
                    </tr>
                    <tr><td colspan="3" style="height: 10px;"></td></tr>
                    <tr>
                        <td>3. Security</td>
                        <td>:</td>
                        <td>___________________</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="sig-left">
                <div class="sig-space"></div>
                <span class="sig-name">___________________</span>
            </td>
            <td class="sig-center">
                <div class="sig-space"></div>
                <span class="sig-name">___________________</span>
            </td>
            <td></td>
        </tr>
    </table>
</body>

</html>