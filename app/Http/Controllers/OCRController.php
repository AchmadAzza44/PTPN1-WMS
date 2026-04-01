<?php
/**
 * OCRController.php — Async OCR via Laravel Queue
 */

namespace App\Http\Controllers;

use App\Jobs\ProcessOcrDocument;
use App\Models\OcrJob;
use App\Models\StockLot;
use App\Models\StockDetail;
use App\Models\InboundTransaction;
use App\Services\OcrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OCRController extends Controller
{
    private OcrService $ocr;

    public function __construct(OcrService $ocr)
    {
        $this->ocr = $ocr;
    }

    // ── Halaman upload ────────────────────────────────────────────────────────

    public function index()
    {
        return view('ocr.index');
    }

    // ── Upload → dispatch async job → redirect ke waiting ────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'document' => 'required|image|max:10240',
            'jenis' => 'required|in:sir20,rss1,do,surat_kuasa',
        ]);

        // Simpan foto ke storage/temp/ocr/ (sementara, bisa di-cleanup)
        $previewPath = $request->file('document')
            ->store('temp/ocr', 'public'); // → storage/app/public/temp/ocr/

        // Buat record OcrJob di DB
        $job = OcrJob::create([
            'jenis' => $request->jenis,
            'type' => $request->input('type', 'inbound'),
            'preview_path' => $previewPath,
            'status' => 'pending',
        ]);

        // Dispatch ke queue (background)
        ProcessOcrDocument::dispatch($job->id);

        // Redirect ke halaman waiting
        return redirect()->route('ocr.waiting', ['id' => $job->id]);
    }

    // ── Halaman waiting — view dengan JS polling ──────────────────────────────

    public function waiting(int $id)
    {
        $job = OcrJob::findOrFail($id);
        return view('ocr.waiting', [
            'jobId' => $job->id,
            'jenis' => $job->jenis,
            'type' => $job->type,
            'previewUrl' => $job->previewUrl(),
            'status' => $job->status,
        ]);
    }

    // ── JSON status endpoint untuk polling ───────────────────────────────────

    public function status(int $id): JsonResponse
    {
        $job = OcrJob::findOrFail($id);

        $payload = [
            'status' => $job->status,
            'jenis' => $job->jenis,
            'type' => $job->type,
            'waktu_s' => $job->waktu_s,
            'error' => $job->error,
        ];

        if ($job->isDone()) {
            $payload['hasil'] = $this->sanitizeHasil($job->hasil ?? []);
            $payload['preview_url'] = $job->previewUrl();
        }

        return response()->json($payload);
    }

    // ── Review page (setelah job done, direct URL) ────────────────────────────

    public function reviewById(int $id)
    {
        $job = OcrJob::findOrFail($id);

        if (!$job->isDone()) {
            return redirect()->route('ocr.waiting', ['id' => $id]);
        }

        return view('ocr.review', [
            'jenis' => $job->jenis,
            'type' => $job->type,
            'imageUrl' => $job->previewUrl(),
            'hasil' => $this->sanitizeHasil($job->hasil ?? []),
            'waktu_s' => $job->waktu_s,
            'mode' => 'ocr',
            'ocr_job_id' => $job->id,
            'blur' => isset($job->blur_score)
                ? ['score' => $job->blur_score, 'status' => $job->blur_score < 60 ? 'blur' : ($job->blur_score < 120 ? 'warning' : 'ok')]
                : ['score' => 999, 'status' => 'ok'],
            'confidence' => $job->confidence ?? ['score' => 100, 'level' => 'high', 'warnings' => []],
            'warning' => $job->warning ?? null,
        ]);
    }

    // ── Form input manual tanpa OCR ───────────────────────────────────────────

    public function manual(Request $request)
    {
        return view('ocr.review', [
            'jenis' => $request->input('jenis', 'sir20'),
            'type' => $request->input('type', 'inbound'),
            'imageUrl' => null,
            'hasil' => [],
            'waktu_s' => null,
            'mode' => 'manual',
            'ocr_job_id' => null,
            'blur' => ['score' => 999, 'status' => 'ok'],
            'confidence' => ['score' => 100, 'level' => 'high', 'warnings' => []],
            'warning' => null,
        ]);
    }

    // ── Simpan data yang sudah diverifikasi ───────────────────────────────────

    public function simpan(Request $request): JsonResponse
    {
        $request->validate([
            'jenis' => 'required|in:sir20,rss1,do,surat_kuasa',
            'hasil' => 'required|array',
            'foto' => 'nullable|image|max:10240',
        ]);

        $jenis = $request->jenis;
        $hasil = $request->hasil;
        $fotoPath = null;
        $ocrJobId = $request->input('ocr_job_id');

        // Copy foto dari temp → arsip permanen
        if ($ocrJobId) {
            $job = OcrJob::find($ocrJobId);
            if ($job && $job->preview_path) {
                $arsipPath = 'arsip/dokumen/' . now()->format('Y/m') . '/' . basename($job->preview_path);
                Storage::disk('public')->copy($job->preview_path, $arsipPath);
                // Hapus file temp setelah dicopy
                Storage::disk('public')->delete($job->preview_path);
                $job->update(['preview_path' => $arsipPath]);
                $fotoPath = $arsipPath;
            }
        } elseif ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('arsip/dokumen/' . now()->format('Y/m'), 'public');
        }

        $saved = match ($jenis) {
            'sir20' => $this->saveSir20($hasil, $fotoPath),
            'rss1' => $this->saveRss1($hasil, $fotoPath),
            'do' => $this->saveDo($hasil, $fotoPath),
            'surat_kuasa' => $this->saveSuratKuasa($hasil, $fotoPath),
            default => null,
        };

        if (!$saved) {
            return response()->json(['success' => false, 'message' => 'Gagal simpan'], 500);
        }

        return response()->json(['success' => true, 'message' => 'Data berhasil disimpan', 'id' => $saved]);
    }

    // ── Sanitize Helpers ──────────────────────────────────────────────────────

    /**
     * Pastikan semua nilai scalar di hasil OCR adalah string/number, bukan array.
     * Konversi field tanggal Indonesia → YYYY-MM-DD untuk input type="date".
     */
    private function sanitizeHasil(array $hasil): array
    {
        $keepAsArray = ['baris', 'nomor_bale', 'nomor_urut_bale'];
        $dateKeys = ['tanggal', 'tanggal_so', 'tanggal_po', 'tanggal_kontrak'];

        foreach ($hasil as $key => $val) {
            if (in_array($key, $keepAsArray))
                continue;
            if (is_array($val)) {
                $val = implode(', ', array_map('strval', $val));
            }
            if (in_array($key, $dateKeys) && is_string($val) && $val !== '') {
                $converted = $this->parseTanggalIndonesia($val);
                $val = $converted ?? $val;
            }
            $hasil[$key] = $val;
        }
        return $hasil;
    }

    /**
     * Konversi tanggal format Indonesia → YYYY-MM-DD.
     * Contoh: "30 Januari 2026" → "2026-01-30", "30/01/2026" → "2026-01-30"
     */
    private function parseTanggalIndonesia(string $s): ?string
    {
        $s = trim($s);
        $bulan = [
            'januari' => 1,
            'februari' => 2,
            'maret' => 3,
            'april' => 4,
            'mei' => 5,
            'juni' => 6,
            'juli' => 7,
            'agustus' => 8,
            'september' => 9,
            'oktober' => 10,
            'november' => 11,
            'desember' => 12,
            'january' => 1,
            'february' => 2,
            'march' => 3,
            'may' => 5,
            'june' => 6,
            'july' => 7,
            'august' => 8,
            'october' => 10,
            'december' => 12,
            'jan' => 1,
            'feb' => 2,
            'mar' => 3,
            'apr' => 4,
            'jun' => 6,
            'jul' => 7,
            'ags' => 8,
            'aug' => 8,
            'sep' => 9,
            'okt' => 10,
            'oct' => 10,
            'nov' => 11,
            'des' => 12,
            'dec' => 12,
        ];
        // "DD MonthName YYYY"
        if (preg_match('/^(\?\?|\d{1,2})\s+([a-zA-Z]+)[^a-zA-Z0-9]*(\d{4})$/', $s, $m)) {
            $monNum = $bulan[strtolower($m[2])] ?? null;
            if ($monNum) {
                $d = ($m[1] === '??' || !is_numeric($m[1])) ? 1 : (int) $m[1];
                return sprintf('%04d-%02d-%02d', (int) $m[3], $monNum, $d);
            }
        }
        // "DD/MM/YYYY" | "DD-MM-YYYY" | "DD.MM.YYYY"
        if (preg_match('/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})$/', $s, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s))
            return $s;
        return null;
    }

    // ── Simpan ke DB (TODO: sesuaikan model) ─────────────────────────────────

    private function saveSir20(array $data, ?string $foto): ?int
    {
        DB::beginTransaction();
        try {
            $ticketNumber = $data['no_surat'] ?? 'SIR20-' . time();
            $totalKg = $data['total_kg'] ?? 0;
            $baris = $data['baris'] ?? [];

            if (empty($baris)) {
                $lotNumber = 'LOT-' . date('ymd') . '-' . rand(1000, 9999);
                $stockLot = StockLot::create([
                    'lot_number' => $lotNumber,
                    'production_year' => date('Y'),
                    'quality_type' => 'SIR 20 SW',
                    'origin_unit' => 'SIR',
                    'status' => 'blue',
                    'inbound_at' => now(),
                ]);

                StockDetail::create([
                    'stock_lot_id' => $stockLot->id,
                    'packaging_type' => 'Pallet',
                    'fdf_number' => $ticketNumber,
                    'bale_range' => '-',
                    'quantity_unit' => $data['total_bale'] ?? floor($totalKg / 35),
                    'net_weight_kg' => $totalKg,
                ]);

                InboundTransaction::create([
                    'stock_lot_id' => $stockLot->id,
                    'ticket_number' => $ticketNumber,
                    'vehicle_plate' => $data['no_kendaraan'] ?? '-',
                    'driver_name' => $data['nama_supir'] ?? '-',
                    'gross_weight' => $totalKg,
                    'tare_weight' => 0,
                    'net_weight' => $totalKg,
                    'weigh_in_at' => now()->subMinutes(30),
                    'weigh_out_at' => now(),
                    'photo_path' => $foto ?: '',
                    'ai_ocr_data' => json_encode($data),
                ]);
            } else {
                $transactionCreated = false;
                foreach ($baris as $b) {
                    $lotNo = $b['no_lot'] ?? ('TMP-' . rand(1000, 9999));
                    $fullLotNumber = $lotNo . '-' . date('dmY');

                    $stockLot = StockLot::create([
                        'lot_number' => $fullLotNumber,
                        'production_year' => date('Y'),
                        'quality_type' => 'SIR 20 SW',
                        'origin_unit' => 'SIR',
                        'status' => 'blue',
                        'inbound_at' => now(),
                    ]);

                    StockDetail::create([
                        'stock_lot_id' => $stockLot->id,
                        'packaging_type' => 'Pallet',
                        'fdf_number' => $b['no_peti'] ?? $ticketNumber,
                        'bale_range' => '-',
                        'quantity_unit' => $b['jml_bale'] ?? 0,
                        'net_weight_kg' => $b['berat_kg'] ?? 0,
                    ]);

                    if (!$transactionCreated) {
                        InboundTransaction::create([
                            'stock_lot_id' => $stockLot->id,
                            'ticket_number' => $ticketNumber,
                            'vehicle_plate' => $data['no_kendaraan'] ?? '-',
                            'driver_name' => $data['nama_supir'] ?? '-',
                            'gross_weight' => $totalKg,
                            'tare_weight' => 0,
                            'net_weight' => $totalKg,
                            'weigh_in_at' => now()->subMinutes(30),
                            'weigh_out_at' => now(),
                            'photo_path' => $foto ?: '',
                            'ai_ocr_data' => json_encode($data),
                        ]);
                        $transactionCreated = true;
                    }
                }
            }
            DB::commit();
            return 1;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("OCR saveSir20 Error: " . $e->getMessage());
            return null;
        }
    }

    private function saveRss1(array $data, ?string $foto): ?int
    {
        DB::beginTransaction();
        try {
            $ticketNumber = $data['no_dokumen'] ?? 'RSS1-' . time();
            $jumlahBale = $data['jumlah_bale'] ?? 0;
            $totalKg = $jumlahBale > 0 ? ($jumlahBale * 113) : ($data['berat_netto_total'] ?? 0);
            $mutu = $data['mutu'] ?? 'RSS 1';

            $lotNumber = 'LOT-' . date('ymd') . '-' . rand(1000, 9999);
            $stockLot = StockLot::create([
                'lot_number' => $lotNumber,
                'production_year' => date('Y'),
                'quality_type' => $mutu,
                'origin_unit' => 'RSS',
                'status' => 'blue',
                'inbound_at' => now(),
            ]);

            StockDetail::create([
                'stock_lot_id' => $stockLot->id,
                'packaging_type' => 'Bale',
                'fdf_number' => $ticketNumber,
                'bale_range' => $data['nomor_bale'] ?? '-',
                'quantity_unit' => $data['jumlah_bale'] ?? 0,
                'net_weight_kg' => $totalKg,
            ]);

            InboundTransaction::create([
                'stock_lot_id' => $stockLot->id,
                'ticket_number' => $ticketNumber,
                'vehicle_plate' => $data['no_kendaraan'] ?? '-',
                'driver_name' => $data['pengangkut'] ?? '-',
                'gross_weight' => $totalKg,
                'tare_weight' => 0,
                'net_weight' => $totalKg,
                'weigh_in_at' => now()->subMinutes(30),
                'weigh_out_at' => now(),
                'photo_path' => $foto ?: '',
                'ai_ocr_data' => json_encode($data),
            ]);

            DB::commit();
            return 1;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("OCR saveRss1 Error: " . $e->getMessage());
            return null;
        }
    }

    private function saveDo(array $data, ?string $foto): ?int
    {
        return 1;
    }

    private function saveSuratKuasa(array $data, ?string $foto): ?int
    {
        return 1;
    }
}