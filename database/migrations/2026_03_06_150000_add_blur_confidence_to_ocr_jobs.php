<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ocr_jobs', function (Blueprint $table) {
            $table->float('blur_score')->nullable()->after('waktu_s');
            $table->json('confidence')->nullable()->after('blur_score');
            $table->text('warning')->nullable()->after('confidence');
        });
    }

    public function down(): void
    {
        Schema::table('ocr_jobs', function (Blueprint $table) {
            $table->dropColumn(['blur_score', 'confidence', 'warning']);
        });
    }
};
