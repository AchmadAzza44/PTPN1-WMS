<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Berita Acara - {{ $group->ba_number ?? $group->id }}</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 15mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
        }

        /* ===== HEADER ===== */
        .header-table {
            width: 100%;
            border: none;
            margin-bottom: 5px;
        }

        .header-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .header-left {
            font-size: 9px;
            line-height: 1.3;
        }

        .header-left .regional {
            font-weight: bold;
            font-size: 11px;
        }

        .header-right {
            text-align: right;
            width: 80px;
        }

        .logo-text {
            font-weight: bold;
            font-size: 14px;
            color: #006838;
            font-style: italic;
        }

        /* ===== TITLE ===== */
        .title-section {
            text-align: center;
            margin: 10px 0 5px 0;
        }

        .title-section h2 {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
            text-decoration: underline;
        }

        .title-section .subtitle {
            font-size: 13px;
            font-weight: bold;
            margin: 2px 0 0 0;
            text-decoration: underline;
        }

        .title-section .nomor {
            font-size: 11px;
            margin: 3px 0 10px 0;
        }

        /* ===== CONTENT ===== */
        .content {
            text-align: justify;
            margin-bottom: 10px;
        }

        .info-list {
            width: 100%;
            border: none;
            margin: 5px 0;
        }

        .info-list td {
            border: none;
            padding: 1px 3px;
            vertical-align: top;
            font-size: 11px;
        }

        .info-list .label {
            width: 180px;
            padding-left: 30px;
        }

        .info-list .separator {
            width: 10px;
        }

        .info-list .value {
            /* auto width */
        }

        .info-list .date-col {
            text-align: right;
            width: 130px;
        }

        /* ===== KETERANGAN ===== */
        .keterangan {
            margin: 10px 0;
        }

        .keterangan ul {
            margin: 3px 0;
            padding-left: 50px;
        }

        .keterangan li {
            margin-bottom: 3px;
            text-align: justify;
        }

        /* ===== FOOTER SECTION ===== */
        .closing-text {
            margin: 15px 0 20px 0;
        }

        .signature-table {
            width: 100%;
            border: none;
            margin-top: 10px;
        }

        .signature-table td {
            border: none;
            padding: 2px;
            vertical-align: top;
            text-align: center;
            font-size: 11px;
        }

        .signature-space {
            height: 55px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }

        /* ===== BOTTOM FOOTER ===== */
        .bottom-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 2px solid #006838;
            padding-top: 5px;
            font-size: 8px;
            line-height: 1.3;
        }

        .bottom-footer .akhlak {
            font-weight: bold;
            font-size: 8px;
        }

        .bottom-footer .company {
            font-weight: bold;
            font-size: 9px;
        }

        .bottom-footer .address {
            font-size: 8px;
        }

        .bottom-footer .frn {
            text-align: right;
            font-size: 8px;
        }

        hr.divider {
            border: none;
            border-top: 1px solid #ccc;
            margin: 3px 0;
        }
    </style>
</head>

<body>
    @php
        // Helper: bulan romawi
        $bulanRomawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];

        $dispatchDate = $group->dispatched_at ?? now();
        $bulanAngka = $dispatchDate->format('n');
        $tahun = $dispatchDate->format('Y');
        $nomorBA = $group->ba_number ?? ('IPMG.Bkl/BA/' . str_pad($group->id, 2, '0', STR_PAD_LEFT) . '/' . $bulanRomawi[$bulanAngka] . '/' . $tahun);

        // Kumpulkan jenis mutu dari semua shipments
        $qualityTypes = $group->shipments->flatMap(function($s) {
            return $s->items->map(fn($i) => $i->stockLot->quality_type ?? '-');
        })->unique()->implode(', ');

        // Total berat dari semua shipments
        $totalKg = $group->shipments->sum(function($s) {
            return $s->items->sum('qty_loaded_kg');
        });

        // Total units (SW) dari semua shipment items
        $totalUnits = 0;
        foreach ($group->shipments as $shipment) {
            foreach ($shipment->items as $item) {
                if ($item->stockLot && $item->stockLot->details) {
                    $totalUnits += $item->stockLot->details->sum('quantity_unit');
                }
            }
        }

        // Nama hari Indonesia
        $hariIndo = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $namaHari = $hariIndo[$dispatchDate->format('l')] ?? $dispatchDate->format('l');

        // Bulan Indonesia
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $tglTerbilang = $dispatchDate->format('d') . ' ' . $bulanIndo[$bulanAngka] . ' ' . $tahun;

        // Terbilang hari
        $tglNarasi = $namaHari . ' tanggal ' . $dispatchDate->format('d') . ' bulan ' . $bulanIndo[$bulanAngka] . ' tahun ' . $tahun;

        $buyerName = $group->buyer_name ?? 'Pembeli';
    @endphp

    <!-- ===== HEADER ===== -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                <div class="regional">REGIONAL 7</div>
                <div>Alamat Jl.Teuku Umar No.300, Bandar Lampung</div>
                <div>Telp : 0721-702233 Email : skrh_reg7@ptpn1.co.id</div>
            </td>
            <td class="header-right">
                <div class="logo-text">ptpn1</div>
            </td>
        </tr>
    </table>

    <hr style="border: 1px solid #006838; margin: 3px 0;">

    <!-- ===== TITLE ===== -->
    <div class="title-section">
        <h2>BERITA ACARA</h2>
        <div class="subtitle">PENYERAHAN BARANG KARET MUTU {{ strtoupper($qualityTypes) }}</div>
        <div class="nomor">Nomor : {{ $nomorBA }}</div>
    </div>

    <!-- ===== NARASI ===== -->
    <div class="content">
        <p>Pada hari ini, {{ $tglNarasi }}
            ({{ $dispatchDate->format('d-m-Y') }}) telah selesai dilaksanakan pelayanan pengiriman karet
            Jenis Mutu {{ $qualityTypes }} dari Instalasi Penimbunan
            Minyak dan Gudang (IPMG) PT.Perkebunan Nusantara I Regional 7 Pulau Baai Bengkulu ke
            {{ $buyerName }} dengan penjelasan sbb :</p>
    </div>

    <!-- ===== DETAIL KONTRAK / PO — LOOP SETIAP SHIPMENT ===== -->
    @foreach($group->shipments as $shipmentIdx => $shipment)
        @php
            $contract = $shipment->purchaseOrder->contract ?? null;
            $po = $shipment->purchaseOrder ?? null;
            $shipmentKg = $shipment->items->sum('qty_loaded_kg');
            $shipmentUnits = 0;
            foreach ($shipment->items as $item) {
                if ($item->stockLot && $item->stockLot->details) {
                    $shipmentUnits += $item->stockLot->details->sum('quantity_unit');
                }
            }
        @endphp
        <table class="info-list">
            <tr>
                <td class="label">- &nbsp; Nomor Kontrak / SC</td>
                <td class="separator">:</td>
                <td class="value"><strong>{{ $contract->contract_number ?? '-' }}</strong></td>
                <td class="date-col">tgl. {{ $contract->contract_date ? date('d-m-Y', strtotime($contract->contract_date)) : '-' }}</td>
            </tr>
            <tr>
                <td class="label">- &nbsp; Nomor PO</td>
                <td class="separator">:</td>
                <td class="value">{{ $po->po_number ?? '-' }}</td>
                <td class="date-col">tgl. {{ $po->po_date ? date('d-m-Y', strtotime($po->po_date)) : '-' }}</td>
            </tr>
            <tr>
                <td class="label">- &nbsp; Jumlah menurut PO</td>
                <td class="separator">:</td>
                <td class="value">{{ number_format($shipmentKg, 0, ',', '.') }} Kg ({{ $shipmentUnits > 0 ? $shipmentUnits . ' SW' : '-' }})</td>
                <td class="date-col"></td>
            </tr>
        </table>

        @if($shipmentIdx < count($group->shipments) - 1)
            <hr class="divider">
        @endif
    @endforeach

    <hr class="divider">

    <!-- ===== TOTAL & PENGANGKUT ===== -->
    <table class="info-list">
        <tr>
            <td class="label">- &nbsp; Jumlah diangkut/dikirim</td>
            <td class="separator">:</td>
            <td class="value"><strong>{{ number_format($totalKg, 0, ',', '.') }} Kg ({{ $totalUnits > 0 ? $totalUnits . ' SW' : '-' }})</strong></td>
            <td class="date-col"></td>
        </tr>
        <tr>
            <td class="label">- &nbsp; Pengirim Barang</td>
            <td class="separator">:</td>
            <td class="value">PTPN I Regional 7 IPMG P.Baai BKL</td>
            <td class="date-col"></td>
        </tr>
        <tr>
            <td class="label">- &nbsp; Pengangkut/Penerima</td>
            <td class="separator">:</td>
            <td class="value">{{ $buyerName }} atas kuasa {{ $group->transporter_name }}</td>
            <td class="date-col"></td>
        </tr>
    </table>

    <!-- ===== NOMOR SURAT KUASA (Multiple) ===== -->
    @php
        $suratKuasaList = $group->shipments
            ->pluck('surat_kuasa_number')
            ->filter()
            ->values();
    @endphp
    @if($suratKuasaList->count() > 0)
        <table class="info-list">
            @foreach($suratKuasaList as $skIdx => $skNumber)
                <tr>
                    <td class="label">{{ $skIdx === 0 ? '- &nbsp; Nomor Surat Kuasa' : '' }}</td>
                    <td class="separator">{{ $skIdx === 0 ? ':' : '' }}</td>
                    <td class="value">{{ $skNumber }}</td>
                    <td class="date-col">tgl. {{ $dispatchDate->format('d-m-Y') }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <!-- ===== KETERANGAN ===== -->
    <div class="keterangan">
        <table class="info-list">
            <tr>
                <td class="label">- &nbsp; Keterangan</td>
                <td class="separator">:</td>
                <td class="value"></td>
            </tr>
        </table>
        <ul>
            <li>Barang dikirim melalui Angkutan Darat dalam kondisi baik
                (tidak mentah tidak basah dan tidak berjamur).</li>
            <li>Jumlah barang dalam keadaan cukup telah di cek oleh petugas
                dan pengangkut (kerusakan / kekurangan bukan merupakan
                tanggung jawab pihak PTPN I Regional 7).</li>
            <li>Pada saat pemuatan cuaca dalam keadaan cerah (tidak
                gerimis/hujan).</li>
        </ul>
    </div>

    <!-- ===== CLOSING ===== -->
    <div class="closing-text">
        <p>Demikian Berita Acara ini dibuat untuk dipergunakan seperlunya.</p>
    </div>

    <!-- ===== TANDA TANGAN ===== -->
    <table class="signature-table">
        <tr>
            <td style="width: 33%; text-align: left;">
                <br>
                Yang Menerima<br>
                {{ $buyerName }}
            </td>
            <td style="width: 34%;"></td>
            <td style="width: 33%; text-align: right;">
                Bengkulu, {{ $dispatchDate->format('d') }} {{ $bulanIndo[$bulanAngka] }} {{ $tahun }}<br>
                Krani IPMG P.Baai BKL
            </td>
        </tr>
        <tr>
            <td style="height: 55px;"></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td style="text-align: left;">
                <span class="signature-name">{{ strtoupper($group->manager_name ?? '_______________') }}</span>
            </td>
            <td></td>
            <td style="text-align: right;">
                <span class="signature-name">{{ strtoupper($group->krani_name ?? '_______________') }}</span>
            </td>
        </tr>
    </table>

    <table class="signature-table" style="margin-top: 10px;">
        <tr>
            <td colspan="3" style="text-align: center;">
                Mengetahui/Menyetujui<br>
                Koordinator<br>
                IPMG P.Baai Bengkulu
            </td>
        </tr>
        <tr>
            <td colspan="3" style="height: 55px;"></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center;">
                <span class="signature-name">{{ strtoupper($group->manager_name ?? '_______________') }}</span>
            </td>
        </tr>
    </table>

    <!-- ===== BOTTOM FOOTER ===== -->
    <div class="bottom-footer">
        <div class="akhlak">AKHLAK- Amanah, Kompeten, Harmonis, Loyal, Adaptif, Kolaboratif</div>
        <div class="company">PT PERKEBUNAN NUSANTARA I REGIONAL 7</div>
        <div class="address"><strong>Kantor Penghubung Bengkulu :</strong></div>
        <div class="address">Jl. Pangeran Natadija Km 7 No.65 Bengkulu – 38225 Propvinsi Bengkulu Telp.:0736–21302 Fax.:0736-21302</div>
    </div>
</body>

</html>