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

        // Jalur ByPass untuk hosting yang tidak memiliki symlink
        return url('/cloud-storage/' . $this->preview_path);
    }
}
