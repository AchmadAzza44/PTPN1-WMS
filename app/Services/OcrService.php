<?php
/**
 * OcrService.php
 * Service untuk memanggil WMS-OCR API dari Laravel
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrService
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('ocr.api_url', 'http://localhost:8000');
        $this->timeout = config('ocr.timeout', 60);
    }

    /**
     * OCR dari path file di server
     */
    public function processFile(string $filePath, string $jenis): array
    {
        if (!file_exists($filePath)) {
            return $this->errorResponse("File tidak ditemukan: {$filePath}");
        }

        $base64 = base64_encode(file_get_contents($filePath));
        return $this->processBase64($base64, $jenis);
    }

    /**
     * OCR dari UploadedFile Laravel
     */
    public function processUpload($uploadedFile, string $jenis): array
    {
        $base64 = base64_encode(file_get_contents($uploadedFile->getRealPath()));
        return $this->processBase64($base64, $jenis);
    }

    /**
     * OCR dari base64 string
     */
    public function processBase64(string $base64, string $jenis): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/ocr", [
                    'foto_base64' => $base64,
                    'jenis' => $jenis,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    return [
                        'success' => true,
                        'jenis' => $data['jenis'],
                        'waktu_s' => $data['waktu_s'],
                        'hasil' => $data['hasil'],
                        'error' => null,
                        'blur' => $data['blur'] ?? null,
                        'confidence' => $data['confidence'] ?? null,
                        'warning' => $data['warning'] ?? null,
                    ];
                }
                return $this->errorResponse($data['error'] ?? 'OCR gagal');
            }

            return $this->errorResponse("HTTP {$response->status()}: {$response->body()}");

        } catch (\Exception $e) {
            Log::error("OcrService error: " . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Health check ke OCR server
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function errorResponse(string $msg): array
    {
        Log::warning("OcrService: {$msg}");
        return ['success' => false, 'hasil' => [], 'error' => $msg, 'waktu_s' => 0];
    }
}
