<?php

namespace App\Jobs;

use App\Models\OcrJob;
use App\Services\OcrService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessOcrDocument implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 120; // sekunder

    public function __construct(protected int $ocrJobId)
    {
    }

    public function handle(OcrService $ocrService): void
    {
        $job = OcrJob::find($this->ocrJobId);
        if (!$job) {
            Log::error("OcrJob #{$this->ocrJobId} not found");
            return;
        }

        $job->update(['status' => 'processing']);

        // Resolve path absolut dari preview_path (public disk)
        $absolutePath = Storage::disk('public')->path($job->preview_path);

        if (!file_exists($absolutePath)) {
            $job->update(['status' => 'failed', 'error' => 'File preview tidak ditemukan']);
            return;
        }

        try {
            $result = $ocrService->processFile($absolutePath, $job->jenis);

            if ($result['success']) {
                $updateData = [
                    'status' => 'done',
                    'hasil' => $result['hasil'],
                    'waktu_s' => $result['waktu_s'],
                    'error' => null,
                    'blur_score' => $result['blur']['score'] ?? null,
                    'confidence' => $result['confidence'] ?? null,
                    'warning' => $result['warning'] ?? null,
                ];

                // Kalau foto blur tapi masih lolos (warning), tetap done tapi dengan flag
                $job->update($updateData);
                $confScore = $result['confidence']['score'] ?? 'N/A';
                $blurStatus = $result['blur']['status'] ?? 'N/A';
                Log::info("OcrJob #{$this->ocrJobId} done in {$result['waktu_s']}s | confidence={$confScore} blur={$blurStatus}");
            } elseif (isset($result['error']) && str_contains($result['error'], 'buram')) {
                // Foto terlalu blur — status khusus agar bisa retry
                $job->update([
                    'status' => 'blur',
                    'error' => $result['error'],
                    'blur_score' => $result['blur']['score'] ?? null,
                ]);
            } else {
                $job->update([
                    'status' => 'failed',
                    'error' => $result['error'] ?? 'OCR gagal',
                ]);
            }
        } catch (\Exception $e) {
            Log::error("OcrJob #{$this->ocrJobId} exception: " . $e->getMessage());
            $job->update(['status' => 'failed', 'error' => $e->getMessage()]);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("OcrJob #{$this->ocrJobId} failed permanently: " . $e->getMessage());
        OcrJob::where('id', $this->ocrJobId)->update([
            'status' => 'failed',
            'error' => 'Job gagal setelah ' . $this->tries . ' percobaan: ' . $e->getMessage(),
        ]);
    }
}
