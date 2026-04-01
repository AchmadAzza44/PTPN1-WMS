<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Daftarkan perintah artisan khusus (Opsional jika sudah menggunakan load).
     */
    protected $commands = [
        Commands\DailyStockSnapshotCommand::class,
    ];

    /**
     * Definisikan jadwal pengerjaan perintah (Command Schedule).
     */
    protected function schedule(Schedule $schedule): void
    {
        /** * Menjalankan Snapshot Stok Harian Otomatis
         * Waktu: 23:59 (Tepat sebelum pergantian hari)
         * Fungsi: Menghitung Saldo Awal, Masuk, Keluar, dan Saldo Akhir hari tersebut.
         */
        $schedule->command('stock:snapshot')
            ->dailyAt('23:59')
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('Snapshot harian berhasil dibuat otomatis.');
            })
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Snapshot harian GAGAL dibuat.');
            });

        /**
         * Opsional: Membersihkan Log Laravel setiap minggu agar penyimpanan server tidak penuh.
         */
        $schedule->command('logs:clear')->weekly();

        // Cleanup file OCR temp (>24 jam) setiap hari jam 02:00
        $schedule->command('ocr:cleanup --hours=24')->dailyAt('02:00');

    }

    /**
     * Daftarkan perintah untuk aplikasi.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}