<?php
/**
 * config/ocr.php
 * Konfigurasi OCR API server
 */
return [
    'api_url' => env('OCR_API_URL', 'http://localhost:8000'),
    'timeout' => env('OCR_TIMEOUT', 60),
];
