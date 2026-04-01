<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
    Schema::create('daily_stock_snapshots', function (Blueprint $table) {
        $table->id();
        $table->date('report_date'); // Tanggal Laporan
        $table->string('quality_type'); // SIR-20, RSS, dll
        $table->integer('opening_stock')->default(0); // SISA
        $table->integer('inbound_total')->default(0); // MASUK
        $table->integer('outbound_total')->default(0); // KELUAR
        $table->integer('closing_stock')->default(0); // STOK AKHIR
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_stock_snapshots');
    }
};
