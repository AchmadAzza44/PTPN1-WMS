<?php

namespace App\Console\Commands;

use App\Models\OcrJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOcrTempFiles extends Command
{
    protected $signature = 'ocr:cleanup {--hours=24 : Hapus file temp yang lebih dari N jam}';
    protected $description = 'Hapus file OCR sementara (storage/temp/ocr/) yang sudah lama tidak terpakai';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $threshold = now()->subHours($hours);

        // Cari OcrJob lama yang masih punya preview di temp/ocr/ (belum disimpan user)
        $jobs = OcrJob::where('created_at', '<', $threshold)
            ->whereNotNull('preview_path')
            ->where('preview_path', 'like', 'temp/ocr/%')
            ->get();

        $deleted = 0;
        foreach ($jobs as $job) {
            if (Storage::disk('public')->exists($job->preview_path)) {
                Storage::disk('public')->delete($job->preview_path);
                $deleted++;
                $this->line("  Hapus: {$job->preview_path} (job #{$job->id})");
            }
            // Bersihkan preview_path di record
            $job->update(['preview_path' => null]);
        }

        $this->info("Cleanup selesai: {$deleted} file temp dihapus (threshold: {$hours} jam)");
        return self::SUCCESS;
    }
}
