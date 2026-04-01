<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OcrJob extends Model
{
    protected $table = 'ocr_jobs';

    protected $fillable = [
        'jenis',
        'type',
        'preview_path',
        'status',
        'hasil',
        'error',
        'warning',
        'waktu_s',
        'blur_score',
        'confidence',
    ];

    protected $casts = [
        'hasil'      => 'array',
        'confidence' => 'array',
    ];

    /** Status helpers */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }
    public function isDone(): bool
    {
        return $this->status === 'done';
    }
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /** URL preview foto (bisa public disk atau base64 inline) */
    public function previewUrl(): ?string
    {
        if (!$this->preview_path) {
            return null;
        }

        // Jalur ByPass: Coba baca file secara langsung dari server untuk menghindari masalah Symlink di Cloud
        $fullPath = storage_path('app/public/' . $this->preview_path);
        if (file_exists($fullPath)) {
            $type = pathinfo($fullPath, PATHINFO_EXTENSION);
            $data = file_get_contents($fullPath);
            return 'data:image/' . ($type === 'jpg' ? 'jpeg' : $type) . ';base64,' . base64_encode($data);
        }

        return asset('storage/' . $this->preview_path);
    }
}
