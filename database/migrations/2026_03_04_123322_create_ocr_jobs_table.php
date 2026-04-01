<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ocr_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('jenis');                          // sir20 | rss1 | do | surat_kuasa
            $table->string('type')->default('inbound');       // inbound | outbound
            $table->string('preview_path')->nullable();       // storage/temp/ocr/... (sementara)
            $table->string('status')->default('pending');     // pending | processing | done | failed
            $table->json('hasil')->nullable();                // hasil OCR setelah selesai
            $table->text('error')->nullable();                // pesan error jika gagal
            $table->float('waktu_s')->nullable();             // durasi proses OCR
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocr_jobs');
    }
};
