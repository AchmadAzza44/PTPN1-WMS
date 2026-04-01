<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AdvancedDocumentAIService;

class DocumentTypeDetectionTest extends TestCase
{
    private AdvancedDocumentAIService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AdvancedDocumentAIService::class);
    }

    /**
     * Test: RSS 1 document detected from "BUKTI PENGANTAR PENGIRIMAN PRODUKSI JADI" title
     */
    public function test_detects_rss1_from_bukti_pengantar_text()
    {
        $text = "PT PERKEBUNAN NUSANTARA I Regional 7
            KEBUN KETAHUN
            Desa Air Sekayen, Kec. Pinang Raya
            BUKTI PENGANTAR PENGIRIMAN PRODUKSI JADI
            
            No.  Jenis Mutu  No. Faktor   Jumlah Bale   Nomor Bale    Berat (Kg)
            1    RSS 1       366          15            7458-7472     1695
            2    RSS 1       187          49            7473-7521     5537
            3    RSS 1       368          14            7522-7535     1582
            
            JUMLAH: 72    Empat Pulau Rata Rata   8.814 Kg";

        $result = $this->service->detectDocumentType($text);

        $this->assertEquals('RSS 1', $result['type']);
        $this->assertGreaterThanOrEqual(50, $result['confidence']);
        $this->assertNotEmpty($result['signals']);
    }

    /**
     * Test: SIR 20 document detected from "SURAT PENGANTAR PENGIRIMAN KARET (SIR)" title
     */
    public function test_detects_sir20_from_surat_pengantar_karet_text()
    {
        $text = "REGIONAL 7
            Kebun Padang Pelawi
            Alamat Jl. Raya Bengkulu - Manna KM 26.5
            39827
            SURAT PENGANTAR PENGIRIMAN KARET (SIR)
            
            Ke Dst: IPMA/G P. Bwi
            No. Kontrak: 7K17/I/2026.01 73
            
            Nomor Peti  Jenis Mutu  Dikirim  Nomor Lot  Berat Kg
            FDF 306     SIR 20      039      H.I        1,200    36
            FDF 308     SIR 20      -        H.I        1,280    36
            FDF 309     SIR 20      -        H.I        1,200    36
            FDF 513     SIR 20      040      H.I        1,200    36
            FDF 314     SIR 20      -        H.I        1,200    36
            FDF 312     SIR 20      -        H.I        1,280    36
            
            Keterangan: Pita mutu di dalamkemasan Shrink Wrap
            Jumlah KI: 7,560  216";

        $result = $this->service->detectDocumentType($text);

        $this->assertEquals('SIR 20', $result['type']);
        $this->assertGreaterThanOrEqual(50, $result['confidence']);
        $this->assertNotEmpty($result['signals']);
    }

    /**
     * Test: SIR 20 detected from FDF table rows (even without explicit title)
     */
    public function test_detects_sir20_from_fdf_table_rows()
    {
        $text = "Dokumen Pengiriman
            FDF 100    SIR 20    H.I    1,200
            FDF 101    SIR 20    H.I    1,280
            FDF 102    SIR 20    H.I    1,200
            JUMLAH: 3,680 Kg";

        $result = $this->service->detectDocumentType($text);

        $this->assertEquals('SIR 20', $result['type']);
        $this->assertGreaterThanOrEqual(30, $result['confidence']);
    }

    /**
     * Test: RSS 1 detected from sequential table rows
     */
    public function test_detects_rss1_from_sequential_table_rows()
    {
        $text = "Dokumen Pengiriman Produksi Jadi
            No. Faktor  Jumlah
            1    RSS 1       100          15            7458-7472     1695
            2    RSS 1       200          49            7473-7521     5537
            JUMLAH: 7,232";

        $result = $this->service->detectDocumentType($text);

        $this->assertEquals('RSS 1', $result['type']);
        $this->assertGreaterThanOrEqual(30, $result['confidence']);
    }

    /**
     * Test: Returns null for ambiguous/unrecognizable text
     */
    public function test_returns_null_for_ambiguous_text()
    {
        $text = "Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.";

        $result = $this->service->detectDocumentType($text);

        $this->assertNull($result['type']);
        $this->assertEquals(0, $result['confidence']);
    }

    /**
     * Test: Cutting document detection
     */
    public function test_detects_cutting_from_keywords()
    {
        $text = "SURAT PENGANTAR PENGIRIMAN
            No.  Jenis Mutu  Jumlah  Berat
            1    CUTTING A       10      500
            2    CUTTING A       15      750
            JUMLAH: 1,250 Kg";

        $result = $this->service->detectDocumentType($text);

        $this->assertEquals('Cutting', $result['type']);
        $this->assertGreaterThanOrEqual(30, $result['confidence']);
    }

    /**
     * Test: Empty text returns null type
     */
    public function test_empty_text_returns_null()
    {
        $result = $this->service->detectDocumentType('');
        $this->assertNull($result['type']);
        $this->assertEquals(0, $result['confidence']);

        $result = $this->service->detectDocumentType(null);
        $this->assertNull($result['type']);
    }
}
