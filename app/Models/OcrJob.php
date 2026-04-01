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

    /** URL preview foto (public disk) */
    public function previewUrl(): ?string
    {
        return $this->preview_path
            ? asset('storage/' . $this->preview_path)
            : null;
    }
}
