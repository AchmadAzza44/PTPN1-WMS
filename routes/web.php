<?php

use Illuminate\Support\Facades\Route;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\OCRController;
use App\Http\Controllers\ShipmentController;

// Jalur ByPass untuk hosting Free Tier yang tidak mendukung Storage Link
Route::get('/cloud-storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (file_exists($fullPath)) {
        $mime = mime_content_type($fullPath);
        return response()->file($fullPath, ['Content-Type' => $mime]);
    }
    abort(404);
})->where('path', '.*');

Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;
        if ($role === 'operator') {
            return redirect()->route('stocks.index');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    // Web Push Subscriptions
    Route::post('/push-subscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'store'])->name('push.subscribe');

    // In-App Notification API (polling)
    Route::get('/notifications/api', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.api');

    // ╔══════════════════════════════════════════════════════════╗
    // ║  SHARED: Semua role (admin, operator, manager)          ║
    // ╚══════════════════════════════════════════════════════════╝

    // Dashboard — Krani + Petugas
    Route::middleware(['role:admin,operator'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    // Melihat Daftar Stok — Krani + Manager + Operator (semua perlu lihat stok)
    Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');

    // Kelola Stok Manual (CRUD) — Hanya Operator
    Route::middleware(['role:operator'])->group(function () {
        Route::get('/stocks/create', [StockController::class, 'create'])->name('stocks.create');
        Route::post('/stocks', [StockController::class, 'store'])->name('stocks.store');
        Route::get('/stocks/{stock}/edit', [StockController::class, 'edit'])->name('stocks.edit');
        Route::put('/stocks/{stock}', [StockController::class, 'update'])->name('stocks.update');
        Route::put('/stocks/{stock}/lot-number', [StockController::class, 'updateLotNumber'])->name('stocks.update_lot');
        Route::delete('/stocks/{stock}', [StockController::class, 'destroy'])->name('stocks.destroy');
    });

    // Show route setelah CRUD routes agar tidak tertangkap wildcard
    Route::get('/stocks/{stock}', [StockController::class, 'show'])->name('stocks.show');

    // Laporan — Krani + Petugas
    Route::middleware(['role:admin,operator'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/realtime', [ReportController::class, 'realtimeDashboard'])->name('reports.realtime');
        Route::get('/reports/api/data', [ReportController::class, 'apiRealtimeData'])->name('reports.api.data');
        Route::get('/report/daily-pdf', [ReportController::class, 'downloadDailyPDF'])->name('report.daily.pdf');
    });

    // ╔══════════════════════════════════════════════════════════╗
    // ║  KRANI GUDANG (admin): Manajemen Outbound              ║
    // ╚══════════════════════════════════════════════════════════╝
    Route::middleware(['role:admin'])->group(function () {
        // Buat Pengiriman Baru
        Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
        Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');

        // Konfirmasi Detail DO/Qty
        Route::put('/shipments/{id}/update-details', [ShipmentController::class, 'updateDetails'])->name('shipments.update_details');

        // Upload Dokumen Bertandatangan
        Route::post('/shipments/{id}/upload-signed-doc', [ShipmentController::class, 'uploadSignedDoc'])->name('shipments.upload_signed_doc');
    });

    // ╔══════════════════════════════════════════════════════════╗
    // ║  PETUGAS GUDANG (operator): Manajemen Inbound           ║
    // ╚══════════════════════════════════════════════════════════╝
    Route::middleware(['role:operator'])->group(function () {
        // Simpan Data Inbound
        Route::post('/inbound', [App\Http\Controllers\InboundController::class, 'store'])->name('inbound.store');

        // Stock Opname
        Route::resource('stock-opname', \App\Http\Controllers\StockOpnameController::class)->only(['index', 'store']);
    });

    // Data Pengiriman & Verifikasi — Krani + Operator (perlu lihat untuk verifikasi)
    Route::middleware(['role:admin,operator'])->group(function () {
        Route::get('/shipments/verification', [ShipmentController::class, 'indexVerification'])->name('shipments.verification');
        Route::post('/shipments/{id}/verify', [ShipmentController::class, 'verify'])->name('shipments.verify');

        Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');

        // Cetak Surat — setelah verifikasi, Krani/Operator bisa cetak
        Route::get('/shipments/{id}/print-sj', [ShipmentController::class, 'printSuratJalan'])->name('shipments.print_sj');
        Route::get('/shipments/{id}/print-ba', [ShipmentController::class, 'printBeritaAcara'])->name('shipments.print_ba');
        Route::get('/shipments/{id}/print-sjt', [ShipmentController::class, 'printSuratJaminan'])->name('shipments.print_sjt');

        // ⚠️ Wildcard HARUS paling bawah agar tidak menangkap /verification, /print-sj, dll
        Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show')
            ->where('shipment', '[0-9]+'); // Hanya terima angka, bukan string!
    });

    // ╔══════════════════════════════════════════════════════════╗
    // ║  OCR SCAN — Krani (outbound: DO, Surat Kuasa)          ║
    // ║            + Petugas (inbound: SIR20, RSS1)             ║
    // ╚══════════════════════════════════════════════════════════╝
    Route::middleware(['role:admin,operator'])->group(function () {
        Route::get('/ocr', [OCRController::class, 'index'])->name('ocr.index');
        Route::post('/ocr', [OCRController::class, 'store'])->name('ocr.store');
        Route::get('/ocr/manual', [OCRController::class, 'manual'])->name('ocr.manual');
        Route::get('/ocr/waiting/{id}', [OCRController::class, 'waiting'])->name('ocr.waiting');
        Route::get('/ocr/status/{id}', [OCRController::class, 'status'])->name('ocr.status');
        Route::get('/ocr/review/{id}', [OCRController::class, 'reviewById'])->name('ocr.review_by_id');
        Route::post('/ocr/simpan', [OCRController::class, 'simpan'])->name('ocr.simpan');
    });
});

// Auth Routes
use App\Http\Controllers\AuthController;
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Temporary Route to Create User (Run once then delete)
Route::get('/create-user', function () {
    try {
        \App\Models\User::updateOrCreate(
            ['email' => 'operator@ptpn1.co.id'],
            [
                'name' => 'Petugas Gudang',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'operator'
            ]
        );
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@ptpn1.co.id'],
            [
                'name' => 'Krani Gudang',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'admin'
            ]
        );
        return 'Users Created Successfully!<br>1. Krani Gudang (admin@ptpn1.co.id)<br>2. Petugas Gudang (operator@ptpn1.co.id)<br>Password: password';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::get('/test-pdf', function () {
    $data = [
        'contract_no' => '1794/HO-SUPCO/SIR-L/N-I/X/2025',
        'buyer_name' => 'PT. Bitung Gunasejahtera',
        'po_no' => '014/KARET SC/2026',
        // Mengambil data lot yang sudah keluar (tidak berstatus blue)
        'items' => \App\Models\StockLot::with('details')->where('status', '!=', 'blue')->get()
    ];
    $pdf = Pdf::loadView('pdf.testing_ba', $data);
    return $pdf->stream('testing-ba.pdf');
});