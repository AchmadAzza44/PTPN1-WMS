<?php
use App\Http\Controllers\Api\InboundController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OutboundController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\OCRController;
// Jalur agar Petugas Gudang bisa melihat stok dari Android
Route::get('/stocks', [DashboardController::class, 'getStocksApi']);

// Jalur untuk mengirim data hasil foto/scan Nota Timbang
Route::post('/inbound/scan', [InboundController::class, 'store']);

Route::post('/outbound/process', [OutboundController::class, 'store']);

// OCR API — scan dokumen & simpan hasil
Route::prefix('ocr')->group(function () {
    Route::post('/scan', [OCRController::class, 'scan']);
    Route::post('/simpan', [OCRController::class, 'simpan']);
});

use App\Http\Controllers\Api\SyncController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/sync', [SyncController::class, 'index']); // New Sync Endpoint

    Route::post('/inbound/scan', [InboundController::class, 'store']);
    Route::post('/outbound/process', [OutboundController::class, 'store']);
});