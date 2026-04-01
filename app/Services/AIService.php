<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    /**
     * Token Hugging Face Anda. 
     * Sebaiknya simpan di file .env sebagai HUGGING_FACE_TOKEN
     */
    protected $token;

    public function __construct()
    {
        $this->token = env('HUGGING_FACE_TOKEN', 'YOUR_HUGGING_FACE_TOKEN_DISINI');
    }

    /**
     * Membaca angka dari Nota Timbang menggunakan OCR (TrOCR)
     */
    public function scanWeightTicket($imagePath)
    {
        $apiUrl = "https://api-inference.huggingface.co/models/microsoft/trocr-base-printed";
        
        if (!file_exists($imagePath)) {
            return ['error' => 'File gambar tidak ditemukan'];
        }

        $imageData = file_get_contents($imagePath);
        
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withToken($this->token)
            ->withBody($imageData, 'image/jpeg')
            ->post($apiUrl);

        if ($response->failed()) {
            Log::error("AI OCR Error: " . $response->body());
            return ['error' => 'Gagal menghubungi server AI'];
        }

        return $response->json(); // Sekarang Intelephense tidak akan merah lagi
    }

    /**
     * Menghitung jumlah Bale atau Palet menggunakan YOLO
     */
    public function countStockObjects($imagePath)
    {
        $apiUrl = "https://api-inference.huggingface.co/models/hustvl/yolos-tiny";
        
        if (!file_exists($imagePath)) {
            return 0;
        }

        $imageData = file_get_contents($imagePath);
        
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withToken($this->token)
            ->withBody($imageData, 'image/jpeg')
            ->post($apiUrl);

        if ($response->failed()) {
            return 0;
        }

        $data = $response->json();

        // Mengembalikan jumlah objek yang terdeteksi di atas truk
        return is_array($data) ? count($data) : 0;
    }

    public function parseNetWeight($rawText)
{
    // Menggunakan Regex untuk mencari angka yang diikuti atau didahului kata 'Netto' atau 'KG'
    // Contoh input: "PTPN I BENGKULU - NETTO: 7750 KG"
    preg_match('/(?:netto|net|berat)\s*[:=]?\s*(\d+(?:[.,]\d+)?)/i', $rawText, $matches);
    
    if (isset($matches[1])) {
        // Mengubah format koma menjadi titik untuk kalkulasi database
        return (float) str_replace(',', '.', $matches[1]);
    }

    return 0; // Jika tidak ditemukan angka berat
}
}